<?php
namespace tomk79\onionSlice\model;
use renconFramework\dataDotPhp;

class scheduler {

	private $rencon;
	private $project;
	private $realpath_project_data_dir;

	/**
	 * Constructor
	 */
	public function __construct( $rencon, $project_id ){
		$this->rencon = $rencon;

		$projects = new \tomk79\onionSlice\model\projects($this->rencon);
		$this->project = $projects->get( $project_id );

		$this->realpath_project_data_dir = $this->project->get_realpath_project_data_dir();
		if( is_string($this->realpath_project_data_dir) && is_dir($this->realpath_project_data_dir) && !is_dir($this->realpath_project_data_dir.'scheduler/') ){
			$this->rencon->fs()->mkdir($this->realpath_project_data_dir.'scheduler/');
			$this->rencon->fs()->mkdir($this->realpath_project_data_dir.'scheduler/_archives/'); // 過去配信済みの予定を格納する
		}
		return;
	}

	// --------------------------------------
	// 配信タスク

	/**
	 * 新しい配信予約を作成する
	 *
	 * @param Integer|String $release_at リリース予定日時 (例: `1900008700`, `2023-12-31T10:00:00Z`)
	 */
	public function create_schedule( $release_at, $revision ) {
		$task_created_at = gmdate('c');
		$dirname = gmdate('Y-m-d-H-i-s');
		$current_schedule = $this->get_schedule_all() ?? (object) array();

		if( is_int( $release_at ) || preg_match('/^[0-9]*$/', $release_at) ){
			$date = new \DateTimeImmutable('@'.$release_at, new \DateTimeZone("UTC"));
		}else{
			$date = new \DateTimeImmutable($release_at, new \DateTimeZone("UTC"));
		}
		$schedule_id = $date->format('Y-m-d-H-i-s');
		$release_at = $date->format('c');

		if( isset($current_schedule->{$schedule_id}) ){
			return false;
		}

		if( is_dir($this->realpath_project_data_dir.'scheduler/'.urlencode($dirname)) ){
			return false;
		}

		if( !$this->rencon->fs()->mkdir($this->realpath_project_data_dir.'scheduler/'.urlencode($dirname).'/') ){
			return false;
		}

		$current_schedule->{$schedule_id} = (object) array(
			'id' => $schedule_id,
			'revision' => $revision,
			'release_at' => $release_at,
		);
		$current_schedule = (array) $current_schedule;
		ksort($current_schedule);
		$current_schedule = (object) $current_schedule;

		// 古いスタンバイを削除する
		$current_schedule = $this->remove_old_standbies($current_schedule);

		$json = (object) array(
			'uniqid' => uniqid(),
			'type' => 'reserve',
			'properties' => $current_schedule->{$schedule_id},
			'task_created_at' => $task_created_at,
			'expected_results' => $current_schedule,
		);
		$result = dataDotPhp::write_json(
			$this->realpath_project_data_dir.'scheduler/'.urlencode($dirname).'/task.json.php',
			$json
		);
		if( !$result ){
			return false;
		}

		// 古いタスクをアーカイブする
		$this->archive_old_tasks();

		return true;
	}

	/**
	 * 配信予約をキャンセルする
	 *
	 * @param String $schedule_id スケジュールID (例: `2023-12-31-10-00-00`)
	 */
	public function delete_schedule( $schedule_id ) {
		$task_created_at = gmdate('c');
		$dirname = gmdate('Y-m-d-H-i-s');
		$current_schedule = $this->get_schedule_all() ?? (object) array();

		if( !isset($current_schedule->{$schedule_id}) ){
			return false;
		}

		if( is_dir($this->realpath_project_data_dir.'scheduler/'.urlencode($dirname)) ){
			return false;
		}

		if( !$this->rencon->fs()->mkdir($this->realpath_project_data_dir.'scheduler/'.urlencode($dirname).'/') ){
			return false;
		}

		unset($current_schedule->{$schedule_id});

		$current_schedule = (array) $current_schedule;
		ksort($current_schedule);
		$current_schedule = (object) $current_schedule;

		$json = (object) array(
			'uniqid' => uniqid(),
			'type' => 'cancel',
			'properties' => (object) array(
				'id' => $schedule_id,
			),
			'task_created_at' => $task_created_at,
			'expected_results' => $current_schedule,
		);
		$result = dataDotPhp::write_json(
			$this->realpath_project_data_dir.'scheduler/'.urlencode($dirname).'/task.json.php',
			$json
		);
		if( !$result ){
			return false;
		}

		// 古いタスクをアーカイブする
		$this->archive_old_tasks();

		return true;
	}

	/**
	 * アクティブな配信タスクを全件取得する
	 */
	public function get_task_all(){
		$dirs = $this->rencon->fs()->ls($this->realpath_project_data_dir.'scheduler/');
		$rtn = array();
		foreach($dirs as $dirname){
			if( !preg_match('/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}\-[0-9]{2}\-[0-9]{2}\-[0-9]{2}$/', $dirname) ){
				continue;
			}
			$json = dataDotPhp::read_json($this->realpath_project_data_dir.'scheduler/'.urlencode($dirname).'/task.json.php');
			if( !$json ){
				continue;
			}
			$rtn[$dirname] = $json;
		}
		return (object) $rtn;
	}

	/**
	 * 配信タスクを取得する
	 */
	public function get_task($task_id){
		$tasks = $this->get_task_all();
		if( !($tasks->{$task_id} ?? null) ){
			return null;
		}
		$rtn = $tasks->{$task_id};
		$rtn->log = array();

		$realpath_base = $this->realpath_project_data_dir.'scheduler/'.urlencode($task_id).'/';
		$csv = $this->rencon->fs()->read_csv($realpath_base.'log.csv.php');
		if( $csv && is_array($csv) ){
			foreach($csv as $idx => $csv_row){
				if( !$idx ){
					continue;
				}
				$row = (object) array();
				$row->datetime = $csv_row[0];
				$row->remote_addr = $csv_row[1];
				$row->task_id = $csv_row[2];
				$row->result = $csv_row[3];
				$row->message = $csv_row[4];
				array_push($rtn->log, $row);
			}
		}
		return $rtn;
	}

	/**
	 * タスクの処理経過を記録する
	 */
	public function log_task( $task_id, $result, $message ){
		if(!strlen($task_id ?? '')){
			return false;
		}
		$realpath_basedir = $this->realpath_project_data_dir.'scheduler/'.urlencode($task_id).'/';
		if( !is_dir($realpath_basedir) ){
			return false;
		}
		$realpath_log = $realpath_basedir.'/log.csv.php';
		dataDotPhp::write_a($realpath_log, $this->rencon->fs()->mk_csv(array(array(
			date('c'),
			$_SERVER['REMOTE_ADDR'] ?? '',
			$task_id,
			$result,
			$message,
		))));
		return;
	}

	/**
	 * 古いタスクをアーカイブに移動する
	 */
	private function archive_old_tasks(){
		$realpath_base_dir = $this->realpath_project_data_dir.'scheduler/';
		$realpath_archive_dir = $this->realpath_project_data_dir.'scheduler/_archives/';
		$tasks = $this->get_task_all();

		$task_ids = array_keys(get_object_vars($tasks));
		sort($task_ids);

		// 最新のタスクは残す (古くても残す)
		// 最新の `expected_results` が必要なので、消せない。
		array_pop($task_ids);

		$archive_targets = array();
		foreach($task_ids as $task_id){
			$task_created_at = $this->parse_release_at($task_id);
			$task_created_at_time = strtotime($task_created_at);
			if( $task_created_at_time < time() - (10*24*60*60) ){
				array_push($archive_targets, $task_id);
			}
		}

		// アーカイブ対象のディレクトリを、 `_archives` に移動する。
		foreach($archive_targets as $target_task_id){
			$archive_path = preg_replace('/\-/', '/', $target_task_id);
			$this->rencon->fs()->mkdir_r($realpath_archive_dir.$archive_path);
			$this->rencon->fs()->rename($realpath_base_dir.$target_task_id, $realpath_archive_dir.$archive_path);
		}
	}

	// --------------------------------------
	// スケジュール

	/**
	 * アクティブな配信スケジュールを全件取得する
	 */
	public function get_schedule_all(){
		$all_tasks = $this->get_task_all();
		$keys = array_keys(get_object_vars($all_tasks));
		if( !count($keys) ){
			return (object) array();
		}
		$last_key = $keys[count($keys)-1] ?? '';
		if( !$last_key ){
			return (object) array();
		}
		$last_task = $all_tasks->{$last_key} ?? null;
		$rtn = (object) $last_task->expected_results ?? null;
		return $rtn;
	}

	/**
	 * 配信スケジュール情報を取得する
	 */
	public function get_schedule($schedule_id){
		$all_schedule = $this->get_schedule_all();
		return $all_schedule->{$schedule_id} ?? null;
	}

	/**
	 * 現在配信中のスケジュールIDを取得する
	 */
	public function get_current_schedule_id(){
		$all_tasks = $this->get_schedule_all();
		$keys = array_keys(get_object_vars($all_tasks));
		sort($keys);
		$now = time();
		$current_schedule_id = null;
		foreach($keys as $key){
			$schedule_released_at = $this->parse_release_at($key);
			$schedule_released_at_time = strtotime($schedule_released_at);
			if($schedule_released_at_time > $now){
				break;
			}
			$current_schedule_id = $key;
		}
		return $current_schedule_id;
	}

	/**
	 * 古いスタンバイ(配信スケジュール)を削除する
	 */
	private function remove_old_standbies($current_schedule){
		$target_schedule_ids = array_keys(get_object_vars($current_schedule));
		sort($target_schedule_ids);

		foreach($target_schedule_ids as $idx => $target_schedule_id){
			$schedule_released_at = $this->parse_release_at($target_schedule_id);
			$schedule_released_at_time = strtotime($schedule_released_at);
			if( $schedule_released_at_time > time() ){
				unset($target_schedule_ids[$idx]);
			}
		}

		// 過去最新のリリーススケジュールを含め、3世代分を残す
		for( $i = 0; $i < 3; $i ++ ){
			array_pop($target_schedule_ids);
		}

		// 削除対象となったスタンバイを削除する
		$target_ids = $target_schedule_ids;
		foreach($target_schedule_ids as $target_schedule_id){
			unset($current_schedule->{$target_schedule_id});
		}
		return $current_schedule;
	}


	// --------------------------------------
	// Utils

	/**
	 * リリース予約のディレクトリ名をパースする
	 */
	public function parse_release_at( $dir ){
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
}
