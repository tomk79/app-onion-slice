<?php
namespace tomk79\onionSlice\helpers;

/**
 * Utility
 */
class utils {

	/** $renconオブジェクト */
	private $rencon;

	/**
	 * Constructor
	 *
	 * @param object $rencon $renconオブジェクト
	 */
	public function __construct( $rencon ){
		$this->rencon = $rencon;
	}

	/**
	 * method が post 以外だったら、 405 エラーのJSONを出力する
	 */
	public function api_post_only(){
		if( $this->rencon->req()->get_method() != 'post' ){
			header('Content-type: text/json');
			header("HTTP/1.0 405 Method Not Allowed");
			echo json_encode(array(
				"result" => false,
				"message" => "Method Not Allowed",
			));
			exit;
		}
	}

}
