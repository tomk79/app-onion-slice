<?php
/**
 * test
 */
class resetTest extends PHPUnit\Framework\TestCase{
	private $fs;

	public function setUp() : void{
		mb_internal_encoding('UTF-8');
		$this->fs = new tomk79\filesystem();
	}


	/**
	 * reset
	 */
	public function testReset(){
		$this->assertEquals( 1, 1 );
		if( is_dir(__DIR__.'/../dist/onion-slice_files/') ){
			$this->fs->rm(__DIR__.'/../dist/onion-slice_files/');
		}
		$this->assertTrue( !$this->fs->is_dir(__DIR__.'/../dist/onion-slice_files/') );
	}

	/**
	 * Create test data
	 */
	public function testCreateData(){
		$this->fs->mkdir(__DIR__.'/../dist/onion-slice_files/');

		ob_start();
		?>
<<?= '?php' ?> header('HTTP/1.1 404 Not Found'); echo('404 Not Found');exit(); <?= '?' ?>>
{
    "zzzzzzzzzzz-zzzzzzzzz-zzzzzzzzz": {
	}
}
		<?php
		$this->fs->save_file(__DIR__.'/../dist/onion-slice_files/api_keys.json.php', ob_get_clean());

		$this->assertEquals( 1, 1 );
	}

}
