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
		if( is_string($this->realpath_project_data_dir) && is_dir($this->realpath_project_data_dir) && !is_dir($this->realpath_project_data_dir.'schedule/') ){
			$this->rencon->fs()->mkdir($this->realpath_project_data_dir.'schedule/');
			$this->rencon->fs()->mkdir($this->realpath_project_data_dir.'schedule/_archives/'); // 過去配信済みの予定を格納する
		}
		return;
	}

	/**
	 * アクティブな配信スケジュールを全件取得する
	 */
	public function get_schedule_all(){
		$rtn = array();
		$dirs = $this->rencon->fs()->ls($this->realpath_project_data_dir.'schedule/');
		foreach( $dirs as $dir ){
			if( $dir == '_archives' ){
				// アーカイブ済みの予約は除外
				continue;
			}
			$schedule = (object) array();
			$schedule->release_at = $this->parse_release_at($dir);
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
