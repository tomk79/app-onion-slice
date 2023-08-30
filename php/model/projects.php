<?php
namespace tomk79\onionSlice\model;
use renconFramework\dataDotPhp;

class projects {

	private $rencon;
	private $realpath_env_config_json;
	private $realpath_projects_dir;
	private $projects;

	/**
	 * Constructor
	 */
	public function __construct( $rencon ){
		$this->rencon = $rencon;

		$realpath_private_data_dir = $this->rencon->conf()->realpath_private_data_dir;
		$this->realpath_env_config_json = $realpath_private_data_dir.'projects.json.php';
		$this->realpath_projects_dir = $realpath_private_data_dir.'projects/';

		if( strlen($realpath_private_data_dir ?? '') && is_dir($realpath_private_data_dir) && !is_dir($this->realpath_projects_dir) ){
			$this->rencon->fs()->mkdir( $this->realpath_projects_dir );
		}

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
	 * プロジェクトオブジェクトを取得する
	 */
	public function get($project_id){
		$project_info = $this->get_project($project_id);
		$realpath_project_data_dir = null;
		if( strlen($this->realpath_projects_dir ?? '') && is_dir($this->realpath_projects_dir) ){
			$realpath_project_data_dir = $this->realpath_projects_dir.urlencode($project_id).'/';
		}
		$project = new project($this->rencon, $project_id, $project_info, $realpath_project_data_dir);
		return $project;
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
		$project_info = $this->projects->{$project_id} ?? false;
		if( !$project_info ){
			return false;
		}

		$project_info = (object) $project_info;
		if(!strlen($project_info->name ?? '')){ $project_info->name = '---'; }
		if(!strlen($project_info->type ?? '')){ $project_info->type = 'directory'; }
		if(!strlen($project_info->url ?? '')){ $project_info->url = null; }
		if(!strlen($project_info->url_admin ?? '')){ $project_info->url_admin = null; }
		if(!strlen($project_info->realpath_base_dir ?? '')){ $project_info->realpath_base_dir = null; }
		if(!strlen($project_info->remote ?? '')){ $project_info->remote = null; }
		if(!strlen($project_info->staging ?? '')){ $project_info->staging = null; }
		return $project_info;
	}

	/**
	 * プロジェクト情報を更新する
	 */
	public function set_project( $project_id, $project_info ){
		if( !strlen($project_id ?? '') ){
			return false;
		}

		$project_info = (object) $project_info;
		if(!strlen($project_info->name ?? '')){ $project_info->name = '---'; }
		if(!strlen($project_info->type ?? '')){ $project_info->type = 'directory'; }
		if(!strlen($project_info->url ?? '')){ $project_info->url = null; }
		if(!strlen($project_info->url_admin ?? '')){ $project_info->url_admin = null; }
		if(!strlen($project_info->realpath_base_dir ?? '')){ $project_info->realpath_base_dir = null; }
		if(!strlen($project_info->remote ?? '')){ $project_info->remote = null; }
		if(!strlen($project_info->staging ?? '')){ $project_info->staging = null; }
		return $this->projects->{$project_id} = $project_info;
	}

	/**
	 * プロジェクト情報を削除する
	 */
	public function delete_project( $project_id ){
		if( !isset($this->projects->{$project_id}) ){
			return false;
		}
		unset($this->projects->{$project_id});
		if( strlen($this->realpath_projects_dir ?? '') && is_dir($this->realpath_projects_dir.urlencode($project_id).'/') ){
			$this->rencon->fs()->rm( $this->realpath_projects_dir.urlencode($project_id).'/' );
		}
		return true;
	}
}
