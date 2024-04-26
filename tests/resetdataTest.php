<?php
/**
 * test
 */
class resetdataTest extends PHPUnit\Framework\TestCase{
	private $fs;

	public function setUp() : void{
		mb_internal_encoding('UTF-8');
		$this->fs = new tomk79\filesystem();
	}


	/**
	 * reset
	 */
	public function testReset(){
		clearstatcache();

		$this->fs->rm(__DIR__.'/testdata/htdocs/');
		$this->assertFalse( $this->fs->is_dir(__DIR__.'/testdata/htdocs/') );

		$this->fs->chmod_r(__DIR__.'/testdata/git-remote/' , 0777);
		$this->fs->rm(__DIR__.'/testdata/git-remote/');
		$this->assertFalse( $this->fs->is_dir(__DIR__.'/testdata/git-remote/') );

		$this->fs->rm(__DIR__.'/testdata/web-server/');
		$this->assertFalse( $this->fs->is_dir(__DIR__.'/testdata/web-server/') );

		$this->fs->chmod_r(__DIR__.'/testdata/web-front/' , 0777);
		$this->fs->rm(__DIR__.'/testdata/web-front/');
		$this->assertFalse( $this->fs->is_dir(__DIR__.'/testdata/web-front/') );

		$this->fs->rm(__DIR__.'/testdata/memo.json');

		clearstatcache();
		sleep(1);
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

		$this->fs->mkdir_r(__DIR__.'/testdata/web-front/onion-slice--waiter_files/');
		$this->fs->copy(__DIR__.'/../dist/onion-slice--waiter.phar', __DIR__.'/testdata/web-front/onion-slice--waiter.phar');
		$this->assertTrue( is_dir(__DIR__.'/testdata/web-front/onion-slice--waiter_files/') );
	}

	/**
	 * Create test data
	 */
	public function testCreateData(){

		// --------------------------------------
		// 管理ユーザーを作成する
		$this->fs->mkdir_r(__DIR__.'/testdata/htdocs/onion-slice_files/users/');
		ob_start(); ?>
<<?= '?php' ?> header('HTTP/1.1 404 Not Found'); echo('404 Not Found');exit(); <?= '?' ?>>
{
    "name": "Admin User",
    "id": "admin",
    "pw": "------",
    "lang": null,
    "email": null,
    "role": "admin"
}
<?php
		$this->fs->save_file(__DIR__.'/testdata/htdocs/onion-slice_files/users/admin.json.php', ob_get_clean());
		$this->assertTrue( is_file(__DIR__.'/testdata/htdocs/onion-slice_files/users/admin.json.php') );

		// --------------------------------------
		// APIキーを作成する
		ob_start(); ?>
<<?= '?php' ?> header('HTTP/1.1 404 Not Found'); echo('404 Not Found');exit(); <?= '?' ?>>
{
	"12345zzzzz": {
		"key": <?= json_encode( password_hash("12345zzzzzzzzzzz-zzzzzzzzz-zzzzzzzzz", PASSWORD_BCRYPT) ) ?>
	}
}
<?php
		$this->fs->save_file(__DIR__.'/testdata/htdocs/onion-slice_files/api_keys.json.php', ob_get_clean());
		$this->assertTrue( is_file(__DIR__.'/testdata/htdocs/onion-slice_files/api_keys.json.php') );

		// --------------------------------------
		// プロジェクトを作成
		// (スケジューラタイプのプロジェクト)
		$this->fs->mkdir_r(__DIR__.'/testdata/htdocs/onion-slice_files/projects/test--production/schedule/_archives/');

		// --------------------------------------
		// gitリモートを作成
		$memoJson = (object) array(
			"commits" => array(),
		);
		$cd = realpath('.');
		chdir(__DIR__.'/testdata/git-remote/');
		exec('git init');

		ob_start(); ?>
# TEST DATA
<?php
		$this->fs->save_file(__DIR__.'/testdata/git-remote/README.md', ob_get_clean());

		ob_start(); ?>
<p>TEST PAGE: Initial Commit</p>
<?php
		$this->fs->save_file(__DIR__.'/testdata/git-remote/test.html', ob_get_clean());

		exec('git add --all');
		exec('git commit -m "Initial Commit."');

		array_push($memoJson->commits, (object) array(
			"revision" => trim(shell_exec('git log -n 1 --format=%H')),
			"testHtmlContent" => file_get_contents(__DIR__.'/testdata/git-remote/test.html'),
		));

		for($i = 1; $i <= 10; $i ++){
			ob_start(); ?>
<p>TEST PAGE: v<?= $i ?></p>
<?php
			$this->fs->save_file(__DIR__.'/testdata/git-remote/test.html', ob_get_clean());

			exec('git add --all');
			exec('git commit -m "Commit v'.$i.'."');

			array_push($memoJson->commits, (object) array(
				"revision" => trim(shell_exec('git log -n 1 --format=%H')),
				"testHtmlContent" => file_get_contents(__DIR__.'/testdata/git-remote/test.html'),
			));
		}

		chdir($cd);

		$this->fs->save_file(__DIR__.'/testdata/memo.json', json_encode($memoJson, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));

		sleep(1);
	}

}
