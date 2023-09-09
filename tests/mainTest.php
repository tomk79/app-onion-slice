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
	 * web-tomte 実行
	 */
	public function testWebTomte(){
		$result = shell_exec('env'
			.' ONITON_SLICE_API_TOKEN="zzzzzzzzzzz-zzzzzzzzz-zzzzzzzzz"'
			.' ONITON_SLICE_URL="http://localhost:3000/onion-slice.php"'
			.' ONITON_SLICE_PROJECT_ID="test--production"'
			.' php web-tomte/onion-slice--web-tomte.php');
			// TODO: 環境に依存しないテストデータを用意する
		var_dump($result);
		$this->assertEquals( 1, 1 );
	}

}
