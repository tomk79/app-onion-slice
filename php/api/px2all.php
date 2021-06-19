<?php
namespace tomk79\onionSlice\api;

class px2all {

	/**
	 * Pickles 2 の `PX=px2dthelper.get.all` を実行する
	 */
	static public function px2all( $rencon ){
		$px2ctrl = new \tomk79\onionSlice\px2ctrl($rencon);
		$px2proj = $px2ctrl->create_px2agent();
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
