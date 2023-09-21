<?php
namespace tomk79\onionSlice\model;
use renconFramework\dataDotPhp;

class profile {

	private $rencon;
    private $login_user_id;
	private $realpath_profile_json;

	/**
	 * Constructor
	 */
	public function __construct( $rencon ){
		$this->rencon = $rencon;

		$this->login_user_id = $this->rencon->user()->get_user_id();
		$this->realpath_profile_json = $this->rencon->conf()->realpath_private_data_dir.'users/'.urlencode($this->login_user_id).'.json.php';

		return;
	}

	/**
	 * ログインユーザー情報を取得する
	 */
	public function get(){

		$data = (object) array();
		if( is_file( $this->realpath_profile_json ) ){
			$data = dataDotPhp::read_json($this->realpath_profile_json);
		}

		if( !isset($data->name) ){ $data->name = ''; }
		if( !isset($data->id) ){ $data->id = $this->login_user_id; }
		if( !isset($data->pw) ){ $data->pw = null; }
		if( !isset($data->lang) ){ $data->lang = null; }
		if( !isset($data->email) ){ $data->email = null; }
		if( !isset($data->role) ){ $data->role = null; }

        unset($data->pw);

		return $data;
	}

	/**
	 * データを保存する
	 */
	public function update( $data ){
		$dataBefore = (object) array();
		if( is_file( $this->realpath_profile_json ) ){
			$dataBefore = dataDotPhp::read_json($this->realpath_profile_json);
		}

		if( !isset($data->name) ){ $data->name = ''; }
		if( !isset($data->id) ){ $data->id = $this->login_user_id; }
		if( !isset($data->pw) ){ $data->pw = null; }
		if( !isset($data->lang) ){ $data->lang = null; }
		if( !isset($data->email) ){ $data->email = null; }
		if( !isset($data->role) ){ $data->role = null; }

        if( strlen($data->pw ?? '') ){
            $data->pw = $this->rencon->auth()->password_hash( $data->pw );
        }else{
            $data->pw = $dataBefore->pw;
        }

		$result = dataDotPhp::write_json($this->realpath_profile_json, $data);

		return $result;
	}

}
