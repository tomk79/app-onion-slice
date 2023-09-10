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


		// --------------------------------------
		// 配信スケジュールを取得する
		$schedule = $this->api_get_schedule();


		// --------------------------------------
		// 配信コンテンツをスタンバイする
		foreach($schedule->schedule as $schedule_id => $schedule_info){
			$realpath_basedir = $this->fs->get_realpath($this->onion_slice_env->realpath_data_dir.'/standby/'.urlencode($schedule_id).'/');

			$this->fs->mkdir($realpath_basedir);
			clearstatcache();

			if(!is_dir($realpath_basedir)){
				continue;
			}

			$cd = realpath('.');
			chdir($realpath_basedir);

			// git clone する
			// TODO: 指定したリビジョンのみをシャローコピーする方法はないか？
			$stdout = shell_exec('git clone '.escapeshellarg($this->onion_slice_env->git_remote).' ./');
			$stdout = shell_exec('git checkout '.escapeshellarg($schedule_info->revision).'');

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

		$realpath_current_contents_basedir = $this->fs->get_realpath($this->onion_slice_env->realpath_data_dir.'/standby/'.urlencode($current_schedule_info->id).'/');
		exec('ln -s '.escapeshellarg($realpath_current_contents_basedir).' '.escapeshellarg($this->onion_slice_env->realpath_public_root_dir));


		// --------------------------------------
		// 不要になった古いコンテンツを削除する

		// TODO: ここで、不要になった古いコンテンツを削除する

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
