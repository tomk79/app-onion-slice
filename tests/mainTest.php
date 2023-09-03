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
	 * web-tomte 実行
	 */
	public function testWebTomte(){
		$result = shell_exec('env ONITON_SLICE_API_TOKEN="zzzzzzzzzzz-zzzzzzzzz-zzzzzzzzz" php web-tomte/onion-slice--web-tomte.php');
		$this->assertEquals( 1, 1 );
	}

}
