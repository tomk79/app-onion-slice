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
		$this->fs->rm(__DIR__.'/testdata/htdocs/');
		$this->assertFalse( $this->fs->is_dir(__DIR__.'/testdata/htdocs/') );

		$this->fs->rm(__DIR__.'/testdata/git-remote/');
		$this->assertFalse( $this->fs->is_dir(__DIR__.'/testdata/git-remote/') );

		$this->fs->rm(__DIR__.'/testdata/web-server/');
		$this->assertFalse( $this->fs->is_dir(__DIR__.'/testdata/web-server/') );
	}

	/**
	 * Setup test env
	 */
	public function testSetupTestEnv(){
		$this->fs->mkdir_r(__DIR__.'/testdata/htdocs/onion-slice_files/');
		$this->fs->copy(__DIR__.'/../dist/onion-slice.php', __DIR__.'/testdata/htdocs/onion-slice.php');
		$this->assertTrue( is_dir(__DIR__.'/testdata/htdocs/onion-slice_files/') );
		$this->assertTrue( is_file(__DIR__.'/testdata/htdocs/onion-slice.php') );

		$this->fs->mkdir_r(__DIR__.'/testdata/git-remote/');
		$this->assertTrue( is_dir(__DIR__.'/testdata/git-remote/') );

		$this->fs->mkdir_r(__DIR__.'/testdata/web-server/');
		$this->assertTrue( is_dir(__DIR__.'/testdata/web-server/') );
	}

	/**
	 * Create test data
	 */
	public function testCreateData(){
		ob_start();
		?>
<<?= '?php' ?> header('HTTP/1.1 404 Not Found'); echo('404 Not Found');exit(); <?= '?' ?>>
{
    "zzzzzzzzzzz-zzzzzzzzz-zzzzzzzzz": {
	}
}
		<?php
		$this->fs->save_file(__DIR__.'/testdata/htdocs/onion-slice_files/api_keys.json.php', ob_get_clean());

		$this->assertEquals( 1, 1 );

		sleep(1);
	}

}
