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

	/**
	 * 新しい配信予約を作成する
	 *
	 * @param Integer|String $release_at リリース予定日時 (例: `1900008700`, `2023-12-31T10:00:00Z`)
	 */
	public function create_schedule( $release_at, $revision ) {
		$task_created_at = gmdate('c');
		$dirname = gmdate('Y-m-d-H-i-s');

		if( is_int( $release_at ) || preg_match('/^[0-9]*$/', $release_at) ){
			$date = new \DateTimeImmutable('@'.$release_at, new \DateTimeZone("UTC"));
		}else{
			$date = new \DateTimeImmutable($release_at, new \DateTimeZone("UTC"));
		}

		if( is_dir($this->realpath_project_data_dir.'scheduler/'.urlencode($dirname)) ){
			return false;
		}

		if( !$this->rencon->fs()->mkdir($this->realpath_project_data_dir.'scheduler/'.urlencode($dirname).'/') ){
			return false;
		}

		$json = (object) array(
			'id' => uniqid(),
			'type' => 'reserve',
			'properties' => (object) array(
				'revision' => $revision,
				'release_date' => $date->format('c'),
			),
			'task_created_at' => $task_created_at,
			'expected_results' => array(),
		);
		$result = dataDotPhp::write_json(
			$this->realpath_project_data_dir.'scheduler/'.urlencode($dirname).'/task.json.php',
			$json
		);
		if( !$result ){
			return false;
		}

		return true;
	}

	/**
	 * 配信予約をキャンセルする
	 *
	 * @param String $schedule_id スケジュールID (例: `2023-12-31-10-00-00`)
	 */
	public function delete_schedule( $schedule_id ) {
		$dirname = $schedule_id;

		if( !is_dir($this->realpath_project_data_dir.'scheduler/'.urlencode($dirname)) ){
			return false;
		}

		if( !$this->rencon->fs()->rm($this->realpath_project_data_dir.'scheduler/'.urlencode($dirname).'/') ){
			return false;
		}

		return true;
	}

	/**
	 * アクティブな配信スケジュールを全件取得する
	 */
	public function get_schedule_all(){
		$rtn = array();
		$dirs = $this->rencon->fs()->ls($this->realpath_project_data_dir.'scheduler/');
		foreach( $dirs as $dir ){
			if( $dir == '_archives' ){
				// アーカイブ済みの予約は除外
				continue;
			}
			$schedule = (object) array();
			$schedule->id = $dir;
			$schedule->release_at = $this->parse_release_at($dir);

			$json = dataDotPhp::read_json($this->realpath_project_data_dir.'scheduler/'.urlencode($dir).'/task.json.php');
			$schedule->revision = $json->properties->revision ?? null;

			$rtn[$dir] = $schedule;
		}
		return $rtn;
	}

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
