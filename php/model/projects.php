<?php
namespace tomk79\onionSlice\model;
use renconFramework\dataDotPhp;

class projects {

	private $rencon;
	private $realpath_env_config_json;
	private $projects;

	/**
	 * Constructor
	 */
	public function __construct( $rencon ){
		$this->rencon = $rencon;

		$this->realpath_env_config_json = $this->rencon->conf()->realpath_private_data_dir.'projects.json.php';

		$data = $this->read();
		$this->projects = $data->projects ?? new \stdClass();

		return;
	}

	/**
	 * データを読み込む
	 */
	private function read(){

		$data = (object) array();
		if( is_file( $this->realpath_env_config_json ) ){
			$data = dataDotPhp::read_json($this->realpath_env_config_json);
		}

		$data->projects = $data->projects ?? new \stdClass();

		return $data;
	}

	/**
	 * データを保存する
	 */
	public function save(){

		$projects = (array) $this->projects ?? array();
		uasort( $projects, function($a, $b){
			if( strtolower($a->name) > strtolower($b->name) ){
				return 1;
			}elseif( strtolower($a->name) < strtolower($b->name) ){
				return -1;
			}
			return 0;
		} );

		$data = (object) array(
			"projects" => $projects,
		);

		$result = dataDotPhp::write_json($this->realpath_env_config_json, $data);

		return $result;
	}

	/**
	 * プロジェクト一覧を取得する
	 */
	public function get_projects(){
		return $this->projects;
	}

	/**
	 * プロジェクト情報を取得する
	 */
	public function get_project( $project_id ){
		return $this->projects->{$project_id} ?? false;
	}

	/**
	 * プロジェクト情報を更新する
	 */
	public function set_project( $project_id, $project ){
		return $this->projects->{$project_id} = $project;
	}

	/**
	 * プロジェクトのベースディレクトリは空ディレクトリか？
	 */
	public function is_project_base_dir_empty( $project_id ){
		$project_info = $this->get_project( $project_id );
		if( !$project_info ){
			return false;
		}
		if( !strlen($project_info->realpath_base_dir ?? '') ){
			return false;
		}
		if( !is_dir($project_info->realpath_base_dir) ){
			return false;
		}

		$ls = $this->rencon->fs()->ls( $project_info->realpath_base_dir );
		if( count($ls) ){
			return false;
		}

		return true;
	}

	/**
	 * プロジェクト情報を削除する
	 */
	public function delete_project( $project_id ){
		if( !isset($this->projects->{$project_id}) ){
			return false;
		}
		unset($this->projects->{$project_id});
		return true;
	}
}
