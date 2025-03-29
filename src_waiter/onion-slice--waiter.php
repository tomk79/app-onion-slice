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
	private $api_request_header = array();

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

		$this->onion_slice_env = $onion_slice_env ?? (object) array();

		$this->api_request_header = array(
			'Content-Type: application/x-www-form-urlencoded',
			'X-API-KEY: '.($this->onion_slice_env->api_key ?? ''),
		);
		if( strlen($this->onion_slice_env->api_basic_auth ?? '') ){
			array_push($this->api_request_header, 'Authorization: Basic '.base64_encode($this->onion_slice_env->api_basic_auth));
		}
	}

	public function fs(){return $this->fs;}
	public function req(){return $this->req;}

	/**
	 * 処理を実行する
	 */
	public function run(){
		echo '======================================='."\n";
		echo '- Onion Slice - Web Waiter - Started'."\n";
		echo "\n";

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

		// 配信する
		// NOTE: 前のプロセスが スタックしている または 進行中 の場合でも、配信処理が滞るのは望ましくないので、
		// lock() の前にも実行しておく。
		$this->publish();


		// --------------------------------------
		// 処理を開始
		// 排他ロックを開始する
		if( !$this->lock('main') ){
			trigger_error('Application locked. Other process is progress...');
			exit();
		}


		// --------------------------------------
		// 配信スケジュールを取得する
		$tasks = $this->api_get_scheduler_tasks();


		// --------------------------------------
		// 状態情報を取得する
		$status = $this->read_status();
		if( !$status ){
			$status = (object) array(
				'last_task_created_at' => null,
			);
		}
		$last_task_timestamp = $this->id2timestamp($status->last_task_created_at ?? null);

		// --------------------------------------
		// 配信タスクを処理する
		echo '==========================='."\n";
		echo 'Executing tasks'."\n";
		echo "\n";

		$task_id = null;
		$task_info = null;
		foreach($tasks->tasks as $task_id => $task_info){
			$this->touch_lockfile('main');

			set_time_limit(3*60*60);

			echo '-----------'."\n";
			echo 'Task '.($task_id ?? '').' ('.($task_info->type ?? '').')'."\n";

			if( $this->id2timestamp($task_id) <= $last_task_timestamp ){
				echo '  -> skipped.'."\n";
				echo "\n";
				continue;
			}

			switch($task_info->type){
				case "reserve":
					// --------------------------------------
					// 配信予約の追加
					$realpath_basedir = $this->fs->get_realpath($this->onion_slice_env->realpath_data_dir.'/standby/'.urlencode($task_info->properties->id).'/');
					$this->fs->mkdir($realpath_basedir);
					if(!is_dir($realpath_basedir)){
						echo '  -> [ERROR] making directory failed.'."\n";
						// 配信タスクの処理結果を報告する
						$this->api_send_report_scheduler_task($task_id, false, '[ERROR] making directory failed.');
						continue 2;
					}

					$this->deploy($realpath_basedir, $task_info->properties->revision);

					echo '  -> succeeded.'."\n";

					// 配信タスクの処理結果を報告する
					$this->api_send_report_scheduler_task($task_id, true, 'Reservation successful.');
					break;

				case "update":
					// --------------------------------------
					// 配信予約の更新
					// TODO: 未実装
					echo '  -> [ERROR] remove failed.'."\n";

					// 配信タスクの処理結果を報告する
					$this->api_send_report_scheduler_task($task_id, false, 'Failed to update '.$task_info->properties->id.'.');
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
					if($result){
						echo '  -> removed.'."\n";
						// 配信タスクの処理結果を報告する
						$this->api_send_report_scheduler_task($task_id, true, 'Success to remove '.$task_info->properties->id.'.');
					}else{
						echo '  -> [ERROR] remove failed.'."\n";
						// 配信タスクの処理結果を報告する
						$this->api_send_report_scheduler_task($task_id, false, 'Failed to remove '.$task_info->properties->id.'.');
					}
					break;

				case "asap":
					// --------------------------------------
					// 割り込み即時配信
					// TODO: 未実装
					echo '  -> [ERROR] update failed.'."\n";

					// 配信タスクの処理結果を報告する
					$this->api_send_report_scheduler_task($task_id, false, 'Failed to update '.$task_info->properties->id.'.');
					break;

				case "rollback":
					// --------------------------------------
					// 巻き戻し
					// TODO: 未実装
					echo '  -> [ERROR] rollback failed.'."\n";

					// 配信タスクの処理結果を報告する
					$this->api_send_report_scheduler_task($task_id, false, 'Failed to rollback '.$task_info->properties->id.'.');
					break;
			}

			clearstatcache();

			$status->last_task_created_at = $task_id;
			$this->save_status($status);

			echo "\n";
		}

		echo "\n";
		echo "\n";

		set_time_limit(30);


		// --------------------------------------
		// 処理結果の整合性をチェックする
		if($task_info){

			echo '==========================='."\n";
			echo 'Checking expected'."\n";
			echo "\n";

			// 未展開のリリース予約を展開する
			echo 'reserved...'."\n";
			foreach($task_info->expected_results as $schedule_id => $schedule_info){
				set_time_limit(3*60*60);

				$realpath_release_reservation_dir = $this->fs->get_realpath($this->onion_slice_env->realpath_data_dir.'/standby/'.urlencode($schedule_id).'/');
				if( !is_dir($realpath_release_reservation_dir) ){
					$this->fs->mkdir($realpath_release_reservation_dir);
				}

				$this->deploy($realpath_release_reservation_dir, $schedule_info->revision);
			}

			// 削除されたはずののリリース予約を削除する
			echo 'removed...'."\n";
			$realpath_basedir = $this->fs->get_realpath($this->onion_slice_env->realpath_data_dir.'/standby/');
			$reserved_dirs = $this->fs->ls($realpath_basedir);
			foreach($reserved_dirs as $reserved_dir){
				set_time_limit(60);

				if( !($task_info->expected_results->{$reserved_dir} ?? null) ){
					$this->fs->chmod_r($realpath_basedir.$reserved_dir.'/', 0777, 0777);
					$this->fs->rm($realpath_basedir.$reserved_dir.'/');
				}
			}

			echo '  -> OK.'."\n";
			echo ''."\n";
			echo "\n";
		}

		set_time_limit(30);


		// --------------------------------------
		// 配信する
		$this->publish();


		// --------------------------------------
		// 終了
		if( !$this->unlock('main') ){
			trigger_error('Failed to unlock Application lock.');
		}

		echo "\n";
		echo "\n";
		echo '======================================='."\n";
		echo '- Onion Slice - Web Waiter - finished'."\n";
		echo "\n";

		exit();
	}



	/**
	 * APIから配信タスク一覧を取得する
	 */
	private function api_get_scheduler_tasks(){
		$json = $this->api_call(
			'proj.'.urlencode($this->onion_slice_env->project_id).'.get_scheduler_tasks',
			array(
			),
			array(
				'method' => 'GET',
			));
		$rtn = json_decode($json);
		return $rtn;
	}


	/**
	 * APIに配信タスクの処理結果を報告する
	 */
	private function api_send_report_scheduler_task($task_id, $result, $message){
		$json = $this->api_call(
			'proj.'.urlencode($this->onion_slice_env->project_id).'.report_scheduler_task',
			array(
				'id' => $task_id ?? '',
				'result' => ($result ? 1 : 0),
				'message' => $message ?? '',
			),
			array(
				'method' => 'POST',
			));
		$rtn = json_decode($json);
		return $rtn;
	}

	/**
	 * APIをコールする
	 */
	private function api_call($route, $params = array(), $options = array()){
		$options = (object) $options;
		$stdout = null;
		if( !preg_match('/^https?\:\/\//i', $this->onion_slice_env->api_endpoint) && is_file($this->onion_slice_env->api_endpoint) ){
			$cmd = array();
			array_push($cmd, $this->get_cmd('php'));
			array_push($cmd, escapeshellarg($this->onion_slice_env->api_endpoint));
			foreach( $params as $key => $val ){
				array_push($cmd, '--'.$key);
				array_push($cmd, escapeshellarg($val));
			}
			array_push($cmd, escapeshellarg($route));
			$stdout = shell_exec(implode(' ', $cmd));
		}else{
			$stdout = file_get_contents(
				$this->onion_slice_env->api_endpoint.'?api='.urlencode($route),
				false,
				stream_context_create(array(
					'http' => array(
						'method'=> $options->method ?? 'POST',
						'header'=> implode("\r\n", $this->api_request_header),
						'content' => http_build_query($params)),
					),
				));
		}
		return $stdout;
	}


	/**
	 * コード一式をデプロイする
	 */
	private function deploy($realpath_basedir, $revision){
		$cd = realpath('.');
		chdir($realpath_basedir);

		$current_revision = null;
		$stdout = '';

		if( file_exists('./.git') ){
			// すでにいずれかのバージョンで展開済みの場合
			// 現在のリビジョン番号を確認する
			$current_revision = shell_exec($this->get_cmd('git').' show -s --format=%H');
			$current_revision = trim($current_revision ?? '');
		}

		if( !$current_revision || $revision !== $current_revision ){
			// 期待するリビジョンが展開されていない場合
			// git clone する
			// 指定したリビジョンのみをシャローコピーする。
			$stdout .= shell_exec($this->get_cmd('git').' init');
			$stdout .= shell_exec($this->get_cmd('git').' fetch --depth 1 '.escapeshellarg($this->onion_slice_env->git_remote).' '.escapeshellarg($revision).'');
			$stdout .= shell_exec($this->get_cmd('git').' reset --hard FETCH_HEAD');

			// composer install する
			if( is_file('./composer.json') ){
				$stdout .= shell_exec($this->get_cmd('php').' '.$this->get_cmd('composer').' install');
			}

			// post-deploy-cmd を実行する
			if( !is_null( $this->onion_slice_env->scripts->{'post-deploy-cmd'} ?? null ) ){
				$commands = $this->onion_slice_env->scripts->{'post-deploy-cmd'};
				if( is_string($commands) ){
					$commands = array($commands);
				}
				foreach($commands as $command){
					$command = preg_replace('/^\@php\s/', $this->get_cmd('php').' ', $command);
					$stdout .= shell_exec($command);
				}
			}
		}

		chdir($cd);
		return true;
	}

	/**
	 * リリース予約フォルダを公開する
	 */
	private function publish(){

		echo '==========================='."\n";
		echo 'Publishing reserved directory'."\n";
		echo "\n";

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
			echo 'Target schedule: '.($current_schedule_id)."\n";

			$realpath_current_contents_basedir = $this->fs->get_realpath($this->onion_slice_env->realpath_data_dir.'/standby/'.urlencode($current_schedule_id).'/');
			exec('ln -nfs '.escapeshellarg($realpath_current_contents_basedir).' '.escapeshellarg($this->onion_slice_env->realpath_public_symlink));
		}

		clearstatcache(true);

		echo '  -> OK.'."\n";
		echo "\n";
		echo "\n";

		return;
	}


	/**
	 * 状態情報を取得する
	 */
	private function read_status(){
		if( !is_file($this->onion_slice_env->realpath_data_dir.'/logs/status.json') ){
			return null;
		}
		$str_json = file_get_contents($this->onion_slice_env->realpath_data_dir.'/logs/status.json');
		$status = json_decode($str_json);
		return $status;
	}

	/**
	 * 状態情報を保存する
	 */
	private function save_status($status){
		$result = $this->fs->save_file($this->onion_slice_env->realpath_data_dir.'/logs/status.json', json_encode($status));
		return $result;
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
	 * コマンドのパスを取得する
	 */
	private function get_cmd($cmd){
		if( $cmd == 'php' ){
			return $this->onion_slice_env->commands->{$cmd} ?? (strlen(PHP_BINARY ?? '') ? PHP_BINARY : null) ?? $cmd;
		}
		return $this->onion_slice_env->commands->{$cmd} ?? $cmd;
	}

	/**
	 * リリース予約のディレクトリ名をパースする
	 */
	private function parse_release_at( $dir ){
		if( !preg_match('/^([0-9]{4})\-([0-9]{2})\-([0-9]{2})\-([0-9]{2})\-([0-9]{2})\-([0-9]{2})$/', $dir??'', $matched) ){
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

	/**
	 * タスクID文字列をタイムスタンプに返還する
	 */
	private function id2timestamp( $dir ){
		if( !is_string($dir) || !strlen($dir ?? '') ){
			return null;
		}
		$datestr = $this->parse_release_at($dir);
		if(!$datestr){
			$datestr = $dir;
		}
		$date = new \DateTimeImmutable(
			$datestr,
			new \DateTimeZone("UTC")
		);
		$timestamp = $date->getTimestamp();
		return $timestamp;
	}
}
