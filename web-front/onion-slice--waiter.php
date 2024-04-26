<?php
require_once(__DIR__.'/../vendor/autoload.php');

$app = new app();
$app->run();
exit();


/**
 * Application main class
 */
class app {

	private $fs;
	private $req;
	private $onion_slice_env;

	/**
	 * Constructor
	 */
	public function __construct(){
		$this->fs = new \tomk79\filesystem();
		$this->req = new \tomk79\request();

		$realpath_onion_slice_env = $this->req->get_cli_option('--json');
		if( !strlen($realpath_onion_slice_env ?? '') ){
			trigger_error('Environment information JSON not provided.');
			exit();
		}
		if( !is_file($realpath_onion_slice_env) || !is_readable($realpath_onion_slice_env) ){
			trigger_error('Environment information JSON not exists or not readable.');
			exit();
		}
		$onion_slice_env_json = file_get_contents($realpath_onion_slice_env);
		$onion_slice_env = json_decode($onion_slice_env_json);

		$this->onion_slice_env = $onion_slice_env;
	}

	public function fs(){return $this->fs;}
	public function req(){return $this->req;}

	/**
	 * 処理を実行する
	 */
	public function run(){
		clearstatcache();

		if( !is_dir($this->onion_slice_env->realpath_data_dir) ){
			trigger_error('Data directory not exists.');
			exit();
		}
		if( !is_writable($this->onion_slice_env->realpath_data_dir) ){
			trigger_error('Data directory not writable.');
			exit();
		}

		$this->fs->mkdir($this->onion_slice_env->realpath_data_dir.'/standby/');
		$this->fs->mkdir($this->onion_slice_env->realpath_data_dir.'/logs/');
		$this->fs->mkdir($this->onion_slice_env->realpath_data_dir.'/app_lock/');

		if( !$this->lock('main') ){
			trigger_error('Application locked. Other process is progress...');
			exit();
		}


		// --------------------------------------
		// 配信スケジュールを取得する
		$schedule = $this->api_get_schedule();


		// --------------------------------------
		// 配信コンテンツをスタンバイする
		foreach($schedule->schedule as $schedule_id => $schedule_info){
			$this->touch_lockfile('main');

			$realpath_basedir = $this->fs->get_realpath($this->onion_slice_env->realpath_data_dir.'/standby/'.urlencode($schedule_id).'/');

			$this->fs->mkdir($realpath_basedir);
			clearstatcache();

			if(!is_dir($realpath_basedir)){
				continue;
			}

			$cd = realpath('.');
			chdir($realpath_basedir);

			// git clone する
			// 指定したリビジョンのみをシャローコピーする。
			$stdout = shell_exec('git init');
			$stdout = shell_exec('git fetch --depth 1 '.escapeshellarg($this->onion_slice_env->git_remote).' '.escapeshellarg($schedule_info->revision).'');
			$stdout = shell_exec('git reset --hard FETCH_HEAD');

			// TODO: ここでスタンバイ完了したことを報告する

			chdir($cd);
		}


		// --------------------------------------
		// 配信する

		// 過去でかつ最新の配信スケジュールIDを特定する
		$now = time();
		$current_schedule_timestamp = 0;
		$current_schedule_info = null;
		foreach($schedule->schedule as $schedule_id => $schedule_info){
			$release_at = new \DateTimeImmutable(
				$schedule_info->release_at,
				new \DateTimeZone("UTC")
			);
			$timestamp_release_at = $release_at->getTimestamp();
			if( $timestamp_release_at > $now ){
				// 配信予定時刻が未来な場合はスキップ
				continue;
			}
			if( $timestamp_release_at > $current_schedule_timestamp ){
				// これまでに見つけた配信済みコンテンツよりも未来の配信予定時刻なら、上書き
				$current_schedule_info = $schedule_info;
				continue;
			}
		}

		clearstatcache(true);

		$realpath_current_contents_basedir = $this->fs->get_realpath($this->onion_slice_env->realpath_data_dir.'/standby/'.urlencode($current_schedule_info->id).'/');
		exec('ln -nfs '.escapeshellarg($realpath_current_contents_basedir).' '.escapeshellarg($this->onion_slice_env->realpath_public_dir->production->realpath));

		clearstatcache(true);


		// --------------------------------------
		// 不要になった古いコンテンツを削除する

		// TODO: ここで、不要になった古いコンテンツを削除する

		if( !$this->unlock('main') ){
			trigger_error('Failed to unlock Application lock.');
		}

		exit();
	}



	/**
	 * 配信スケジュールを取得する
	 */
	private function api_get_schedule(){
		$schedule_json = file_get_contents(
			$this->onion_slice_env->url.'?api=proj.'.urlencode($this->onion_slice_env->project_id).'.get_schedule',
			false,
			stream_context_create(array(
				'http' => array(
					'method'=> 'GET',
					'header'=> implode("\r\n", array(
						'Content-Type: application/x-www-form-urlencoded',
						'X-API-KEY: '.$this->onion_slice_env->api_token,
					)),
				),
			)));
		$schedule = json_decode($schedule_json);
		return $schedule;
	}



	// ----------------------------------------------------------------------------
	// アプリケーションロック

	/**
	 * アプリケーションロックする。
	 *
	 * @param string $app_name アプリケーションロック名
	 * @param int $expire 有効時間(秒) (省略時: 60秒)
	 * @return bool ロック成功時に `true`、失敗時に `false` を返します。
	 */
	public function lock( $app_name, $expire = 60 ){
		$lockfilepath = $this->onion_slice_env->realpath_data_dir.'/app_lock/'.urlencode($app_name ?? "").'.lock.php';
		$timeout_limit = 5;

		if( !$this->fs()->is_dir( dirname( $lockfilepath ) ) ){
			$this->fs()->mkdir_r( dirname( $lockfilepath ) );
		}

		// PHPのFileStatusCacheをクリア
		clearstatcache();

		$i = 0;
		while( $this->is_locked( $app_name, $expire ) ){
			$i ++;
			if( $i >= $timeout_limit ){
				return false;
				break;
			}
			sleep(1);

			// PHPのFileStatusCacheをクリア
			clearstatcache();
		}
		$src = '';
		$src .= '<'.'?php header(\'HTTP/1.1 404 Not Found\'); echo(\'404 Not Found\');exit(); ?'.'>'."\n";
		$src .= 'ProcessID='.getmypid()."\r\n";
		$src .= @date( 'c', time() )."\r\n";
		$RTN = $this->fs()->save_file( $lockfilepath , $src );
		return	$RTN;
	}

	/**
	 * アプリケーションロックされているか確認する。
	 *
	 * @param string $app_name アプリケーションロック名
	 * @param int $expire 有効時間(秒) (省略時: 60秒)
	 * @return bool ロック中の場合に `true`、それ以外の場合に `false` を返します。
	 */
	public function is_locked( $app_name, $expire = 60 ){
		$lockfilepath = $this->onion_slice_env->realpath_data_dir.'/app_lock/'.urlencode($app_name ?? "").'.lock.php';
		$lockfile_expire = $expire;

		// PHPのFileStatusCacheをクリア
		clearstatcache();

		if( $this->fs()->is_file($lockfilepath) ){
			if( ( time() - filemtime($lockfilepath) ) > $lockfile_expire ){
				// 有効期限を過ぎていたら、ロックは成立する。
				return false;
			}
			return true;
		}
		return false;
	}

	/**
	 * アプリケーションロックを解除する。
	 *
	 * @param string $app_name アプリケーションロック名
	 * @return bool ロック解除成功時に `true`、失敗時に `false` を返します。
	 */
	public function unlock( $app_name ){
		$lockfilepath = $this->onion_slice_env->realpath_data_dir.'/app_lock/'.urlencode($app_name ?? "").'.lock.php';

		// PHPのFileStatusCacheをクリア
		clearstatcache();
		if( !$this->fs()->is_file( $lockfilepath ) ){
			return true;
		}

		return unlink( $lockfilepath );
	}

	/**
	 * アプリケーションロックファイルの更新日を更新する。
	 *
	 * @param string $app_name アプリケーションロック名
	 * @return bool 成功時に `true`、失敗時に `false` を返します。
	 */
	public function touch_lockfile( $app_name ){
		$lockfilepath = $this->onion_slice_env->realpath_data_dir.'/app_lock/'.urlencode($app_name ?? "").'.lock.php';

		// PHPのFileStatusCacheをクリア
		clearstatcache();
		if( !$this->fs()->is_file( $lockfilepath ) ){
			return false;
		}

		return touch( $lockfilepath );
	}
}
