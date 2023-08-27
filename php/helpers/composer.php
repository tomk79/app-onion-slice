<?php
namespace tomk79\onionSlice\helpers;

/**
 * Composer操作Helper
 */
class composer {

	/** $renconオブジェクト */
	private $rencon;

	/** 設定オブジェクト */
	private $env_config;

	/** プロジェクト情報 */
	private $project_info;

	/**
	 * Constructor
	 *
	 * @param object $rencon $renconオブジェクト
	 * @param object $project_info プロジェクト情報
	 */
	public function __construct( $rencon, $project_info ){
		$this->rencon = $rencon;
		$this->env_config = new \tomk79\onionSlice\model\env_config( $this->rencon );
		$this->project_info = $project_info;
	}


	/**
	 * composer コマンドを実行する
	 *
	 * @param array $composer_command_array Composerコマンドオプション
	 * @return array 実行結果
	 */
	public function composer( $composer_command_array = array() ){
		$rtn = (object) array();

		if( !$this->project_info ) {
			$rtn->result = false;
			$rtn->message = "Project is not defined.";
			return $rtn;
		}

		$path_composer = $this->rencon->fs()->get_realpath($this->rencon->conf()->realpath_private_data_dir.'commands/composer/composer.phar');

		if( !is_file($path_composer) ){
			$this->rencon->fs()->mkdir_r( dirname($path_composer) );
			$this->rencon->fs()->save_file( $path_composer, $this->rencon->resources()->get('resources/composer.phar') );
		}
		$base_dir = $this->project_info->realpath_base_dir;
		$current_dir = realpath('.');

		if( !is_dir($base_dir) ) {
			$rtn->result = false;
			$rtn->message = "Project base dir is not exists.";
			return $rtn;
		}

		$path_composer_home = $this->rencon->conf()->realpath_private_data_dir.'_composer_home/';

		$realpath_php_command = (strlen($this->env_config->commands->php ?? '') ? $this->env_config->commands->php : ($this->rencon->conf()->commands->php ?? 'php'));
		$rtn->command = $realpath_php_command.' '.escapeshellarg($path_composer);
		foreach($composer_command_array as $command){
			$rtn->command .= ' '.escapeshellarg($command);
		}

		chdir($base_dir);

		ob_start();
		$proc = proc_open('export COMPOSER_HOME='.$path_composer_home.'; '.$rtn->command, array(
			0 => array('pipe','r'),
			1 => array('pipe','w'),
			2 => array('pipe','w'),
		), $pipes);

		$io = array();
		foreach($pipes as $idx=>$pipe){
			if($idx){
				$io[$idx] = stream_get_contents($pipe);
			}
			fclose($pipe);
		}
		$return_var = proc_close($proc);
		ob_get_clean();

		chdir($current_dir);


		$rtn->result = true;
		$rtn->exit = $return_var;
		$rtn->stdout = $io[1];
		$rtn->stderr = $io[2];

		header('Content-type: text/json');
		echo json_encode($rtn);
		exit;
	}

}
