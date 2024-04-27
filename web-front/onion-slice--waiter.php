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

		$realpath_onion_slice_env = $this->req->get_cli_option('--env');
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
		// 配信する
		$this->publish();


		// --------------------------------------
		// 配信スケジュールを取得する
		$tasks = $this->api_get_scheduler_tasks();


		// --------------------------------------
		// 配信タスクを処理する
		foreach($tasks->tasks as $task_created_at => $task_info){
			$this->touch_lockfile('main');

			echo '-----------'."\n";
			echo '- '.($task_info->id ?? '').' ('.($task_info->type ?? '').')'."\n";

			switch($task_info->type){
				case "reserve":
					// --------------------------------------
					// 配信予約の追加
					$realpath_basedir = $this->fs->get_realpath($this->onion_slice_env->realpath_data_dir.'/standby/'.urlencode($task_info->properties->id).'/');
					$this->fs->mkdir($realpath_basedir);

					if(!is_dir($realpath_basedir)){
						continue 2;
					}

					$cd = realpath('.');
					chdir($realpath_basedir);

					// git clone する
					// 指定したリビジョンのみをシャローコピーする。
					$stdout = shell_exec('git init');
					$stdout = shell_exec('git fetch --depth 1 '.escapeshellarg($this->onion_slice_env->git_remote).' '.escapeshellarg($task_info->properties->revision).'');
					$stdout = shell_exec('git reset --hard FETCH_HEAD');

					// TODO: ここでタスクの処理結果を報告する

					chdir($cd);
					break;

				case "update":
					// --------------------------------------
					// 配信予約の更新
					// TODO: 未実装
					break;

				case "cancel":
					// --------------------------------------
					// 配信予約のキャンセル
					$realpath_basedir = $this->fs->get_realpath($this->onion_slice_env->realpath_data_dir.'/standby/'.urlencode($task_info->properties->id).'/');
					if(!is_dir($realpath_basedir)){
						continue 2;
					}
					$this->fs->chmod_r($realpath_basedir, 0777, 0777);
					$result = $this->fs->rm($realpath_basedir);
					break;

				case "asap":
					// --------------------------------------
					// 割り込み即時配信
					// TODO: 未実装
					break;

				case "rollback":
					// --------------------------------------
					// 巻き戻し
					// TODO: 未実装
					break;
			}

			clearstatcache();

		}


		// --------------------------------------
		// 配信する
		$this->publish();


		// --------------------------------------
		// 不要になった古いコンテンツを削除する

		// TODO: ここで、不要になった古いコンテンツを削除する

		if( !$this->unlock('main') ){
			trigger_error('Failed to unlock Application lock.');
		}

		exit();
	}



	/**
	 * APIから配信タスク一覧を取得する
	 */
	private function api_get_scheduler_tasks(){
		$schedule_json = file_get_contents(
			$this->onion_slice_env->url.'?api=proj.'.urlencode($this->onion_slice_env->project_id).'.get_scheduler_tasks',
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


	/**
	 * リリース予約フォルダを公開する
	 */
	private function publish(){
		// 過去でかつ最新の配信スケジュールIDを特定する
		$now = time();
		$current_schedule_timestamp = 0;
		$current_schedule_id = null;
		$realpath_standby_basedir = $this->fs->get_realpath($this->onion_slice_env->realpath_data_dir.'/standby/');
		$standbys = $this->fs->ls($realpath_standby_basedir);

		foreach($standbys as $schedule_id){
			$schedule_release_at = $this->parse_release_at($schedule_id);
			if(!$schedule_release_at){
				continue;
			}

			$release_at = new \DateTimeImmutable(
				$schedule_release_at,
				new \DateTimeZone("UTC")
			);
			$timestamp_release_at = $release_at->getTimestamp();
			if( $timestamp_release_at > $now ){
				// 配信予定時刻が未来な場合はスキップ
				continue;
			}
			if( $timestamp_release_at > $current_schedule_timestamp ){
				// これまでに見つけた配信済みコンテンツよりも未来の配信予定時刻なら、上書き
				$current_schedule_id = $schedule_id;
				continue;
			}
		}

		clearstatcache(true);

		if( strlen($current_schedule_id ?? '') ){
			$realpath_current_contents_basedir = $this->fs->get_realpath($this->onion_slice_env->realpath_data_dir.'/standby/'.urlencode($current_schedule_id).'/');
			exec('ln -nfs '.escapeshellarg($realpath_current_contents_basedir).' '.escapeshellarg($this->onion_slice_env->realpath_public_dir->production->realpath));
		}

		clearstatcache(true);

		return;
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



	// ----------------------------------------------------------------------------
	// Utils

	/**
	 * リリース予約のディレクトリ名をパースする
	 */
	private function parse_release_at( $dir ){
		if( !preg_match('/^([0-9]{4})\-([0-9]{2})\-([0-9]{2})\-([0-9]{2})\-([0-9]{2})\-([0-9]{2})$/', $dir, $matched) ){
			return false;
		}
		$y = $matched[1];
		$m = $matched[2];
		$d = $matched[3];
		$h = $matched[4];
		$i = $matched[5];
		$s = $matched[6];
		return $y.'-'.$m.'-'.$d.'T'.$h.':'.$i.':'.$s.'Z';
	}
}
