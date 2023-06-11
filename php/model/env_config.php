<?php
namespace tomk79\onionSlice\model;
use renconFramework\dataDotPhp;

class env_config {

	private $rencon;
	private $realpath_env_config_json;

	public $url_preview;
	public $url_production;
	public $git_url;
	public $git_username;
	public $git_password;

	/**
	 * Constructor
	 */
	public function __construct( $rencon ){
		$this->rencon = $rencon;

		$this->realpath_env_config_json = $this->rencon->conf()->realpath_private_data_dir.'env_config.json.php';

		$data = $this->read();
		$this->url_preview = $data->url_preview ?? null;
		$this->url_production = $data->url_production ?? null;
		$this->git_url = $data->git_url ?? null;
		$this->git_username = $data->git_username ?? null;
		$this->git_password = $data->git_password ?? null;

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

		$result = dataDotPhp::write_json($this->realpath_env_config_json, $data);

		return $result;
	}

}
