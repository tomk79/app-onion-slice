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

		// --------------------------------------
		// git操作
		$cd = realpath('.');
		chdir(__DIR__.'/testdata/git-remote/');

		$current_revision = shell_exec('git log -n 1 --format=%H');

		chdir($cd);
		// / git操作
		// --------------------------------------

		// --------------------------------------
		// 配信予約
		ob_start(); ?>
<<?= '?php' ?> header('HTTP/1.1 404 Not Found'); echo('404 Not Found');exit(); <?= '?' ?>>
{
    "revision": <?= json_encode($current_revision) ?>
}
<?php
		$this->fs->mkdir(__DIR__.'/testdata/htdocs/onion-slice_files/projects/test--production/schedule/2023-01-10-00-00-00/');
		$this->fs->save_file(__DIR__.'/testdata/htdocs/onion-slice_files/projects/test--production/schedule/2023-01-10-00-00-00/schedule.json.php', ob_get_clean());
		// / 配信予約
		// --------------------------------------


		$result = testHelper::shell_exec_onionSlice__webTomte();
		var_dump($result);
		$this->assertEquals( 1, 1 );
	}

}
