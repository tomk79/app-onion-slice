<?php
namespace tomk79\onionSlice\middleware;

class boot {

	/**
	 * セットアップを進行する
	 */
	public function boot( $rencon ){
		$rencon->utils = new \tomk79\onionSlice\helpers\utils($rencon);
		return;
	}

}
