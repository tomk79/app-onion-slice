<?php
namespace tomk79\onionSlice\helpers;

/**
 * crypt
 */
class crypt{

	/** renconオブジェクト */
	private $rencon;

	/** 暗号キー */
	private $crypt_key;

	/** アルゴリズム */
	private $algo;

	/**
	 * Constructor
	 *
	 * @param object $rencon $renconオブジェクト
	 */
	public function __construct( $rencon ){
		$this->rencon = $rencon;
		$this->crypt_key = getenv("APP_KEY");
		$this->algo = 'AES-128-ECB';
	}

	/**
	 * 可逆暗号化する
	 */
	public function encrypt( $data ){
		$rtn = openssl_encrypt($data, $this->algo, $this->crypt_key);
		return $rtn;
	}

	/**
	 * 可逆暗号を復号する
	 */
	public function decrypt( $crypted ){
		$rtn = openssl_decrypt($crypted, $this->algo, $this->crypt_key);
		return $rtn;
	}
}
