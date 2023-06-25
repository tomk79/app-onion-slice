<?php
namespace tomk79\onionSlice\model;
use tomk79\onionSlice\helpers\crypt;
use renconFramework\dataDotPhp;

class env_config {

	private $rencon;
	private $realpath_env_config_json;

	public $commands;
	public $remotes;

	/**
	 * Constructor
	 */
	public function __construct( $rencon ){
		$this->rencon = $rencon;

		$this->realpath_env_config_json = $this->rencon->conf()->realpath_private_data_dir.'env_config.json.php';

		$data = $this->read();
		$this->commands = $data->commands ?? (object) array();
		$this->remotes = $data->remotes ?? (object) array();

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

		if( !isset($data->commands) ){ $data->commands = (object) array(); }
		if( !isset($data->commands->php) ){ $data->commands->php = null; }
		if( !isset($data->commands->git) ){ $data->commands->git = null; }
		if( !isset($data->remotes) ){ $data->remotes = (object) array(); }

		return $data;
	}

	/**
	 * データを保存する
	 */
	public function save(){
		$crypt = new crypt( $this->rencon );

		$data = (object) array();
		$data->commands = $this->commands;
		$data->remotes = $this->remotes;

		$result = dataDotPhp::write_json($this->realpath_env_config_json, $data);

		return $result;
	}

}
