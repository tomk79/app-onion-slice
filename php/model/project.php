<?php
namespace tomk79\onionSlice\model;
use renconFramework\dataDotPhp;

class project {

	private $rencon;
	private $project_id;
	private $project_info;

	/**
	 * Constructor
	 */
	public function __construct( $rencon, $project_id, $project_info ){
		$this->rencon = $rencon;
		$this->project_id = $project_id;
		$this->project_info = $project_info;
		return;
	}

	/**
	 * 動的なプロパティを登録する
	 */
	public function __set( $name, $property ){
		switch($name){
			case 'name':
			case 'type':
			case 'url':
			case 'url_admin':
			case 'realpath_base_dir':
			case 'remote':
			case 'staging':
				$this->project_info->{$name} = $property;
				break;
			default:
				trigger_error($name.' is undefined key on $project.');
				break;
		}
		return;
	}

	/**
	 * 動的に追加されたプロパティを取り出す
	 */
	public function __get( $name ){
		return $this->project_info->{$name} ?? null;
	}

	/**
	 * プロジェクトIDを取得する
	 */
	public function get_project_id(){
		return $this->project_id;
	}

	/**
	 * スケジューラーオブジェクトを生成する
	 */
	public function scheduler(){
		if( $this->type != 'scheduler' ){
			return false;
		}
		$scheduler = new \tomk79\onionSlice\model\scheduler($this->rencon, $this->project_id);
		return $scheduler;
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
