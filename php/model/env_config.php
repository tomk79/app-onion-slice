<?php
namespace tomk79\onionSlice\model;

class env_config {

	private $rencon;
	private $path_env_config_json;

	public $git_url;
	public $git_username;
	public $git_password;

	/**
	 * Constructor
	 */
	public function __construct( $rencon ){
		$this->rencon = $rencon;

		$this->path_env_config_json = $this->rencon->conf()->path_data_dir.'/env_config.json';

		$data = $this->read();
		$this->url_preview = $data->url_preview;
		$this->url_production = $data->url_production;
		$this->git_url = $data->git_url;
		$this->git_username = $data->git_username;
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

		if( !isset($data->url_preview) ){ $data->url_preview = null; }
		if( !isset($data->url_production) ){ $data->url_production = null; }
		if( !isset($data->git_url) ){ $data->git_url = null; }
		if( !isset($data->git_username) ){ $data->git_username = null; }
		if( !isset($data->git_password) ){ $data->git_password = null; }

		return $data;
	}

	/**
	 * データを保存する
	 */
	public function save(){

		$data = (object) array();
		$data->url_preview = $this->url_preview;
		$data->url_production = $this->url_production;
		$data->git_url = $this->git_url;
		$data->git_username = $this->git_username;
		$data->git_password = $this->git_password;

		$result = $this->rencon->fs()->save_file($this->path_env_config_json, json_encode($data));

		return $result;
	}

}
