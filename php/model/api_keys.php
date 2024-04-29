<?php
namespace tomk79\onionSlice\model;
use renconFramework\dataDotPhp;

class api_keys {

	private $rencon;
	private $realpath_api_keys_json;

	/**
	 * Constructor
	 */
	public function __construct( $rencon ){
		$this->rencon = $rencon;
		$this->realpath_api_keys_json = $this->rencon->conf()->realpath_private_data_dir.'api_keys.json.php';
		return;
	}

	/**
	 * APIキーの一覧を取得する
	 */
	public function get_api_keys(){
		$api_keys = dataDotPhp::read_json($this->realpath_api_keys_json);
		if( !$api_keys ){
			return array();
		}
		return array_keys( get_object_vars($api_keys) );
	}

	/**
	 * APIキーを作成する
	 */
	public function create_new_api_key(){
		$api_key = $this->generate_random_string();
		$api_key_initial10 = substr($api_key, 0, 10);
		$new_api_key_info = (object) array(
			'key' => password_hash($api_key, PASSWORD_BCRYPT),
			'permissions' => array(),
			'created_by' => $this->rencon->user()->get_user_id(),
			'created_at' => date('c'),
		);

		$api_keys = dataDotPhp::read_json($this->realpath_api_keys_json);
		if( !$api_keys ){
			$api_keys = (object) array();
		}
		$api_keys->{$api_key_initial10} = $new_api_key_info;
		$result = dataDotPhp::write_json($this->realpath_api_keys_json, $api_keys);
		if( !$result ){
			return false;
		}
		return $api_key;
	}

	/**
	 * APIキーを削除する
	 */
	public function delete_api_key($api_key_initial10){
		$api_keys = dataDotPhp::read_json($this->realpath_api_keys_json);
		unset($api_keys->{$api_key_initial10});
		$result = dataDotPhp::write_json($this->realpath_api_keys_json, $api_keys);
		return $result;
	}

	/**
	 * ランダムな文字列を生成する
	 */
	private function generate_random_string($length = 32) {
		$characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}
}
