<?php
namespace tomk79\onionSlice\api;

class clearcache {

	/**
	 * Pickles 2 のキャッシュを消去する
	 */
	static public function clearcache( $rencon ){
		$pickles2 = new \tomk79\onionSlice\pickles2($rencon);
		$px2proj = $pickles2->create_px2agent();
		$result = $px2proj->clearcache();

		header('Content-type: text/json');
		echo json_encode(array(
			'result' => true,
			'stdout' => $result,
		));
		exit;
	}

}
