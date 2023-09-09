<?php
/**
 * test
 */
class cleaningTest extends PHPUnit\Framework\TestCase{

	/**
	 * ファイルシステムユーティリティ
	 */
	private $fs;

	/**
	 * setup
	 */
	public function setUp() : void{
		$this->fs = new \tomk79\filesystem();
		mb_internal_encoding('utf-8');
		@date_default_timezone_set('Asia/Tokyo');
	}

	/**
	 * 後始末
	 */
	public function testClear(){

		// 変換後ファイルの後始末
		$this->rmdir_f(__DIR__.'/testdata/git-remote/.git');
		$this->assertFalse( is_dir(__DIR__.'/testdata/git-remote/.git') );

		clearstatcache();

	} // testClear()

	/**
	 * フォルダを強制的に削除する
	 */
	private function rmdir_f( $realpath_target ){
		clearstatcache();
		if( $this->fs->is_dir($realpath_target) ){
			$this->fs->chmod_r($realpath_target , 0777);
			if( !$this->fs->rm($realpath_target) ){
				var_dump('Failed to cleaning test remote directory.');
			}
			clearstatcache();
		}
	}
}
