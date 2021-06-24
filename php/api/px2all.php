<?php
namespace tomk79\onionSlice\api;

class px2all {

	/**
	 * Pickles 2 の `PX=px2dthelper.get.all` を実行する
	 */
	static public function px2all( $rencon ){
		$pickles2 = new \tomk79\onionSlice\pickles2($rencon);
		$px2proj = $pickles2->create_px2agent();
		$result = $px2proj->px_command(
			'px2dthelper.get.all',
			'/index.html',
			array()
		);

		header('Content-type: text/json');
		echo json_encode($result);
		exit;
	}

}
