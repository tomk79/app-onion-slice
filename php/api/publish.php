<?php
namespace tomk79\onionSlice\api;

class publish {

	/**
	 * パブリッシュする
	 */
	static public function publish( $rencon ){
		$pickles2 = new \tomk79\onionSlice\pickles2($rencon);
		$px2proj = $pickles2->create_px2agent();
		$result = $px2proj->publish();

		header('Content-type: text/json');
		echo json_encode(array(
			'result' => true,
			'stdout' => $result,
		));
		exit;
	}

}
