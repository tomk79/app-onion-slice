<?php
namespace tomk79\onionSlice\middleware;

class setup {

	/**
	 * セットアップを進行する
	 */
	public function setup_wizard( $rencon ){
		$setup = new \tomk79\onionSlice\setup( $rencon );
		if( !$setup->wizard() ){
			return;
		}
		return;
	}

}
