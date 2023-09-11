<?php
/**
 * test
 */
class mainTest extends PHPUnit\Framework\TestCase{
	private $fs;

	public function setUp() : void{
		mb_internal_encoding('UTF-8');
		require_once(__DIR__.'/php_test_helper/helper.php');
		testHelper::start_built_in_server();
		$this->fs = new tomk79\filesystem();
	}

	/**
	 * ---
	 */
	public function testStandard(){
		$this->assertSame( 1, 1 );
	}

}
