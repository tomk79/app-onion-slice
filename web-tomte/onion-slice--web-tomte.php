<?php

// --------------------------------------
// 環境情報
$onion_slice_env = (object) array(
	"url" => $_SERVER['ONITON_SLICE_URL'] ?? null,
	"realpath_data_dir" => $_SERVER['ONITON_SLICE_DATA_DIR'] ?? realpath(__DIR__).'/'.preg_replace('/\.[a-zA-Z0-9]*$/', '', basename(__FILE__)).'_files/',
	"git_remote" => $_SERVER['ONITON_SLICE_GIT_REMOTE'] ?? null,
	"api_token" => $_SERVER['ONITON_SLICE_API_TOKEN'] ?? null,
	"project_id" => $_SERVER['ONITON_SLICE_PROJECT_ID'] ?? null,
);

$app = new app($onion_slice_env);
$app->run();
exit();


/**
 * Application main class
 */
class app {

	private $onion_slice_env;

	/**
	 * Constructor
	 */
	public function __construct($onion_slice_env){
		$this->onion_slice_env = $onion_slice_env;
	}

	/**
	 * 処理を実行する
	 */
	public function run(){
		clearstatcache();

		if( !is_dir($this->onion_slice_env->realpath_data_dir) ){
			trigger_error('Data directory is not exists.');
			exit();
		}
		if( !is_writable($this->onion_slice_env->realpath_data_dir) ){
			trigger_error('Data directory is not writable.');
			exit();
		}

		if( !is_dir($this->onion_slice_env->realpath_data_dir.'/standby/') ){
			mkdir($this->onion_slice_env->realpath_data_dir.'/standby/');
		}


		// --------------------------------------
		// 配信スケジュールを取得する
		$schedule = $this->api_get_schedule();

		foreach($schedule->schedule as $schedule_id => $schedule_info){
			$realpath_basedir = $this->onion_slice_env->realpath_data_dir.'/standby/'.urlencode($schedule_id).'/';
			if(!is_dir($realpath_basedir)){
				mkdir($realpath_basedir);
				clearstatcache();
			}
			if(!is_dir($realpath_basedir)){
				continue;
			}

			$cd = realpath('.');
			chdir($realpath_basedir);

			// TODO: 指定したリビジョンのみをシャローコピーする方法はないか？
			$stdout = shell_exec('git clone '.escapeshellarg($this->onion_slice_env->git_remote).' ./');
			$stdout = shell_exec('git checkout '.$schedule_info->revision.'');

			chdir($cd);
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

}
