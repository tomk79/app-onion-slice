<?php
/**
 * test
 */
class webWaiterTest extends PHPUnit\Framework\TestCase{
	private $fs;

	public function setUp() : void{
		mb_internal_encoding('UTF-8');
		require_once(__DIR__.'/php_test_helper/helper.php');
		testHelper::start_built_in_server();
		$this->fs = new tomk79\filesystem();
	}

	/**
	 * web-waiter: 初回リリース
	 */
	public function testInitialRelease(){

		$memo = testHelper::get_memo();

		// --------------------------------------
		// 配信予約
		$date = new \DateTimeImmutable('@'.(time() - 3600), new \DateTimeZone("UTC"));
		$dirname = $date->format('Y-m-d-H-i-s');
		ob_start(); ?>
<<?= '?php' ?> header('HTTP/1.1 404 Not Found'); echo('404 Not Found');exit(); <?= '?' ?>>
{
    "revision": <?= json_encode($memo->commits[0]->revision) ?>
}
<?php
		$this->fs->mkdir(__DIR__.'/testdata/htdocs/onion-slice_files/projects/test--production/schedule/'.urlencode($dirname).'/');
		$this->fs->save_file(__DIR__.'/testdata/htdocs/onion-slice_files/projects/test--production/schedule/'.urlencode($dirname).'/schedule.json.php', ob_get_clean());
		// / 配信予約
		// --------------------------------------


		testHelper::shell_exec_onionSlice__webWaiter();

		$this->assertSame( $memo->commits[0]->testHtmlContent, file_get_contents(__DIR__.'/testdata/web-server/production/test.html') );
	}


	/**
	 * web-waiter: 2段階の配信予約
	 */
	public function testWebWaiter(){

		$memo = testHelper::get_memo();

		// --------------------------------------
		// 配信予約
		$date = new \DateTimeImmutable('@'.(time() - 10), new \DateTimeZone("UTC"));
		$dirname = $date->format('Y-m-d-H-i-s');
		ob_start(); ?>
<<?= '?php' ?> header('HTTP/1.1 404 Not Found'); echo('404 Not Found');exit(); <?= '?' ?>>
{
    "revision": <?= json_encode($memo->commits[1]->revision) ?>
}
<?php
		$this->fs->mkdir(__DIR__.'/testdata/htdocs/onion-slice_files/projects/test--production/schedule/'.urlencode($dirname).'/');
		$this->fs->save_file(__DIR__.'/testdata/htdocs/onion-slice_files/projects/test--production/schedule/'.urlencode($dirname).'/schedule.json.php', ob_get_clean());
		// / 配信予約
		// --------------------------------------

		// --------------------------------------
		// 配信予約
		$date = new \DateTimeImmutable('@'.(time() + 7), new \DateTimeZone("UTC"));
		$dirname = $date->format('Y-m-d-H-i-s');
		ob_start(); ?>
<<?= '?php' ?> header('HTTP/1.1 404 Not Found'); echo('404 Not Found');exit(); <?= '?' ?>>
{
    "revision": <?= json_encode($memo->commits[2]->revision) ?>
}
<?php
		$this->fs->mkdir(__DIR__.'/testdata/htdocs/onion-slice_files/projects/test--production/schedule/'.urlencode($dirname).'/');
		$this->fs->save_file(__DIR__.'/testdata/htdocs/onion-slice_files/projects/test--production/schedule/'.urlencode($dirname).'/schedule.json.php', ob_get_clean());
		// / 配信予約
		// --------------------------------------

		sleep(2);

		testHelper::shell_exec_onionSlice__webWaiter();

		// v1 に更新されている。
		clearstatcache(true);
		$this->assertSame( $memo->commits[1]->testHtmlContent, file_get_contents(__DIR__.'/testdata/web-server/production/test.html') );

		sleep(2);

		testHelper::shell_exec_onionSlice__webWaiter();

		// まだ v2 のリリース時刻には達していない。
		clearstatcache(true);
		$this->assertSame( $memo->commits[1]->testHtmlContent, file_get_contents(__DIR__.'/testdata/web-server/production/test.html') );

		sleep(5);

		testHelper::shell_exec_onionSlice__webWaiter();

		// v2 がリリースされている。
		clearstatcache(true);
		$this->assertSame( $memo->commits[2]->testHtmlContent, file_get_contents(__DIR__.'/testdata/web-server/production/test.html') );

		$this->assertSame( 1,1 );
	}

}
