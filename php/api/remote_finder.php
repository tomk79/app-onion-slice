<?php
namespace tomk79\onionSlice\api;

class remote_finder {

	/**
	 * Pickles 2 の `PX=px2dthelper.get.all` を実行する
	 */
	static public function gpi( $rencon ){
		$remoteFinder = new \tomk79\remoteFinder\main(array(
			'default' => $rencon->conf()->path_data_dir.'/project/'
		), array(
			'paths_invisible' => array(
				// '/invisibles/*',
				// '*.hide'
			),
			'paths_readonly' => array(
				'/.git/*',
				'/vendor/*',
			),
		));

		$value = $remoteFinder->gpi( json_decode( $rencon->req()->get_param('gpi_param') ) );

		header('Content-type: text/json');
		echo json_encode($value);
		exit;
	}

}
