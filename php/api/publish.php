<?php
namespace tomk79\onionSlice\api;

class publish {

	/**
	 * パブリッシュする
	 */
	static public function publish( $rencon ){
		$px2ctrl = new \tomk79\onionSlice\px2ctrl($rencon);
		$px2proj = $px2ctrl->create_px2agent();
		$result = $px2proj->publish();

		header('Content-type: text/json');
		echo json_encode(array(
			'result' => true,
			'stdout' => $result,
		));
		exit;
	}

}
