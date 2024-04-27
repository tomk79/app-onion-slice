<?php
class testHelper{

	/**
	 * Start Built in server.
	 */
	static public function start_built_in_server(){
		static $pid;
		if($pid){
			return;
		}
		$WEB_SERVER_HOST = 'localhost';
		$WEB_SERVER_PORT = 3000;
		$WEB_SERVER_DOCROOT = __DIR__.'/../testdata/htdocs/';
		$WEB_SERVER_ROUTER = __DIR__.'/router.php';

		// Command that starts the built-in web server
		$command = sprintf(
			'php -S %s:%d -t %s %s >/dev/null 2>&1 & echo $!',
			$WEB_SERVER_HOST,
			$WEB_SERVER_PORT,
			$WEB_SERVER_DOCROOT,
			$WEB_SERVER_ROUTER
		);

		// Execute the command and store the process ID
		$output = array();
		exec($command, $output);
		$pid = (int) $output[0];

		echo sprintf(
			'%s - Web server started on %s:%d with PID %d',
			date('r'),
			$WEB_SERVER_HOST,
			$WEB_SERVER_PORT,
			$pid
		) . PHP_EOL;

		// Kill the web server when the process ends
		register_shutdown_function(function() use ($pid) {
			echo sprintf('%s - Killing process with ID %d', date('r'), $pid) . PHP_EOL;
			exec('kill ' . $pid);
		});
		return;
	}

	/**
	 * web-waiter を実行する
	 */
	static public function shell_exec_onionSlice__webWaiter() {
		$fs = new \tomk79\filesystem();

		$onion_slice_env = (object) array(
			"url" => 'http://localhost:3000/onion-slice.php',
			"realpath_data_dir" => $fs->get_realpath(__DIR__.'/../testdata/web-front/onion-slice--web-waiter_files/'),
			"realpath_public_dir" => (object) array(
				"production" => (object) array(
					"realpath" => $fs->get_realpath(__DIR__.'/../testdata/web-server/').'production',
				)
			),
			"git_remote" => $fs->get_realpath(__DIR__.'/../testdata/git-remote/.git'),
			"api_token" => "12345zzzzzzzzzzz-zzzzzzzzz-zzzzzzzzz",
			"project_id" => 'test--production',
		);
		$fs->save_file(__DIR__.'/../testdata/web-front/onion-slice--env.json', json_encode($onion_slice_env, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));

		$result = shell_exec(
			// 'php '.__DIR__.'/../../web-front/onion-slice--web-waiter.php'
			__DIR__.'/../testdata/web-front/onion-slice--web-waiter.phar'
			.' --env '.escapeshellarg(__DIR__.'/../testdata/web-front/onion-slice--env.json')
		);
		return $result;
	}

	/**
	 * memo.json を読み込む
	 */
	static public function get_memo(){
		$str_json = file_get_contents(__DIR__.'/../testdata/memo.json');
		$json = json_decode($str_json);
		return $json;
	}
}
