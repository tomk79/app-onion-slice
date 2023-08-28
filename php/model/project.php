<?php
namespace tomk79\onionSlice\model;
use renconFramework\dataDotPhp;

class project {

	private $rencon;
	private $realpath_env_config_json;
	private $project_info;

	/**
	 * Constructor
	 */
	public function __construct( $rencon, $project_info ){
		$this->rencon = $rencon;
		$this->project_info = $project_info;
		return;
	}


	/**
	 * プロジェクトのベースディレクトリは空ディレクトリか？
	 */
	public function is_project_base_dir_empty(){
		if( !$this->project_info ){
			return false;
		}
		if( !strlen($this->project_info->realpath_base_dir ?? '') ){
			return false;
		}
		if( !is_dir($this->project_info->realpath_base_dir) ){
			return false;
		}

		$ls = $this->rencon->fs()->ls( $this->project_info->realpath_base_dir );
		if( count($ls) ){
			return false;
		}

		return true;
	}

	/**
	 * プロジェクトのベースディレクトリが存在するか？
	 */
	public function base_dir_exists(){
		if( !strlen($this->project_info->realpath_base_dir ?? '') ){
			return false;
		}
		if( !is_dir($this->project_info->realpath_base_dir ?? null) ){
			return false;
		}
		return true;
	}


	/**
	 * プロジェクトが composer.json を配置しているか？
	 */
	public function has_composer_json(){
		if( !strlen($this->project_info->realpath_base_dir ?? '') ){
			return false;
		}
		if( !is_dir($this->project_info->realpath_base_dir ?? null) ){
			return false;
		}
		if( !is_file($this->project_info->realpath_base_dir.'/composer.json') ){
			return false;
		}
		return true;
	}

	/**
	 * プロジェクトが .git を配置しているか？
	 */
	public function has_dot_git(){
		if( !strlen($this->project_info->realpath_base_dir ?? '') ){
			return false;
		}
		if( !is_dir($this->project_info->realpath_base_dir ?? null) ){
			return false;
		}
		if( !file_exists($this->project_info->realpath_base_dir.'/.git') ){
			return false;
		}
		return true;
	}
}
