<?php
namespace tomk79\onionSlice\model;

class env_config {

	private $rencon;
	private $path_env_config_json;

	public $git_remote;
	public $git_user_name;
	public $git_password;

	/**
	 * Constructor
	 */
	public function __construct( $rencon ){
		$this->rencon = $rencon;

		$this->path_env_config_json = $this->rencon->conf()->path_data_dir.'/env_config.json';

		$data = $this->read();
		$this->git_remote = $data->git_remote;
		$this->git_user_name = $data->git_user_name;
		$this->git_password = $data->git_password;

		return;
	}

	/**
	 * データを読み込む
	 */
	private function read(){

		$data = (object) array();
		if( is_file( $this->path_env_config_json ) ){
			$json = file_get_contents( $this->path_env_config_json );
			$data = json_decode( $json );
		}

		if( !isset($data->git_remote) ){ $data->git_remote = null; }
		if( !isset($data->git_user_name) ){ $data->git_user_name = null; }
		if( !isset($data->git_password) ){ $data->git_password = null; }

		return $data;
	}

	/**
	 * データを保存する
	 */
	public function save(){

		$data = (object) array();
		$data->git_remote = $this->git_remote;
		$data->git_user_name = $this->git_user_name;
		$data->git_password = $this->git_password;

		$result = $this->rencon->fs()->save_file($this->path_env_config_json, json_encode($data));

		return $result;
	}

}
