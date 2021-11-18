<?php
/**
 * test
 */
class mainTest extends PHPUnit\Framework\TestCase{
	private $fs;

	public function setUp() : void{
		mb_internal_encoding('UTF-8');
		$this->fs = new tomk79\filesystem();
	}


	/**
	 * テスト
	 */
	public function testStandard(){
		$this->assertEquals( 1, 1 );
		if( is_dir(__DIR__.'/../dist/onion-slice/') ){
			$this->fs->rm(__DIR__.'/../dist/onion-slice/');
		}
		$this->assertTrue( !$this->fs->is_dir(__DIR__.'/../dist/onion-slice/') );
	}

}
