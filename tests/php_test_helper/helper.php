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
	 * web-tomte を実行する
	 */
	static public function shell_exec_onionSlice__webTomte() {
		$result = shell_exec('env'
			.' ONITON_SLICE_API_TOKEN="zzzzzzzzzzz-zzzzzzzzz-zzzzzzzzz"'
			.' ONITON_SLICE_DATA_DIR='.json_encode(realpath(__DIR__.'/../testdata/web-tomte_data_dir/').'/', JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)
			.' ONITON_SLICE_PUBLIC_ROOT_DIR='.json_encode(realpath(__DIR__.'/../testdata/web-server/').'/root', JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)
			.' ONITON_SLICE_GIT_REMOTE='.json_encode(realpath(__DIR__.'/../testdata/git-remote/.git'), JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)
			.' ONITON_SLICE_URL="http://localhost:3000/onion-slice.php"'
			.' ONITON_SLICE_PROJECT_ID="test--production"'
			.' php web-tomte/onion-slice--web-tomte.php');
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
