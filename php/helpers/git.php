<?php
namespace tomk79\onionSlice\helpers;

class git{

	private $rencon;
	private $project_info;

	/**
	 * Constructor
	 */
	public function __construct( $rencon, $project_info ){
		$this->rencon = $rencon;
		$this->project_info = $project_info;
	}

	/**
	 * Gitリモートサーバーからデフォルトのブランチ名を取得する
	 */
	public function get_remote_default_branch_name( $git_url = null ) {
		$default = 'master';
		if( !strlen( $git_url ?? '' ) ){
			$git_url = $this->url_bind_confidentials();
		}
		if( !strlen( $git_url ?? '' ) ){
			return $default;
		}
		$result = shell_exec('git ls-remote --symref '.escapeshellarg($git_url).' HEAD');
		if(!is_string($result) || !strlen($result ?? '')){
			return $default;
		}

		if( !preg_match('/^ref\: refs\/heads\/([^\s]+)\s+HEAD/', $result, $matched) ){
			return $default;
		}

		return $matched[1];
	}

	/**
	 * Gitコマンドを実行する
	 *
	 * @param array $git_sub_command Gitコマンドオプション
	 * @return array 実行結果
	 */
	public function git( $git_sub_command ){
		$realpath_pj_git_root = $this->project_info->realpath_base_dir;
		$error_message = false;

		if( !is_array($git_sub_command) ){
			return array(
				'stdout' => '',
				'stderr' => 'Internal Error: Invalid arguments are given.',
				'return' => 1,
			);
		}

		if( !$this->is_valid_command($git_sub_command) ){
			return array(
				'stdout' => '',
				'stderr' => 'Internal Error: Command not permitted.',
				'return' => 1,
			);
		}

		clearstatcache();

		if( !is_dir($realpath_pj_git_root) || !is_dir($realpath_pj_git_root.'.git/') ){
			if( $git_sub_command[0] !== 'init' && $git_sub_command[0] !== 'clone' ){
				// .git がなければ実行させない。
				return array(
					'stdout' => '',
					'stderr' => 'Git is not initialized.',
					'return' => 1,
				);
			}
		}


		foreach($git_sub_command as $idx=>$git_sub_command_row){
			$git_sub_command[$idx] = escapeshellarg($git_sub_command_row);
		}
		$cmd = implode(' ', $git_sub_command);

		$cd = realpath('.');
		chdir($realpath_pj_git_root);


		ob_start();
		$proc = proc_open('git '.$cmd, array(
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

		chdir($cd);

		return array(
			'stdout' => $this->conceal_confidentials($io[1]),
			'stderr' => $this->conceal_confidentials($io[2]),
			'return' => $return_var,
		);
	}

	/**
	 * Gitコマンドに不正がないか確認する
	 */
	private function is_valid_command( $git_sub_command ){

		if( !is_array($git_sub_command) ){
			// 配列で受け取る
			return false;
		}

		// 許可されたコマンド
		switch( $git_sub_command[0] ){
			case 'init':
			case 'clone':
			case 'config':
			case 'status':
			case 'branch':
			case 'log':
			case 'diff':
			case 'show':
			case 'remote':
			case 'fetch':
			case 'checkout':
			case 'add':
			case 'rm':
			case 'reset':
			case 'clean':
			case 'commit':
			case 'merge':
			case 'push':
			case 'pull':
				break;
			default:
				return false;
				break;
		}

		// 不正なオプション
		foreach( $git_sub_command as $git_sub_command_row ){
			if( preg_match( '/^\-\-output(?:\=.*)?$/', $git_sub_command_row ) ){
				return false;
			}
		}

		return true;
	}

	/**
	 * origin をセットする
	 */
	public function set_remote_origin(){
		$git_remote = $this->url_bind_confidentials();
		if( !strlen($git_remote ?? '') ){
			return true;
		}
		$this->git(array('remote', 'add', 'origin', $git_remote));
		$this->git(array('remote', 'set-url', 'origin', $git_remote));
		return true;
	}

	/**
	 * origin を削除する
	 */
	public function clear_remote_origin(){
		$this->git(array('remote', 'remove', 'origin'));
		return true;
	}

	/**
	 * URLに認証情報を埋め込む
	 */
	public function url_bind_confidentials($url = null, $user_name = null, $password = null){
		$env_config = new \tomk79\onionSlice\model\env_config( $this->rencon );

		if( $env_config && !strlen($url ?? '') ){
			$url = $env_config->git_url;
		}
		if( $env_config && !strlen($user_name ?? '') && strlen($env_config->git_username ?? '') ){
			$user_name = $env_config->git_username;
		}
		if( $env_config && !strlen($password ?? '') && strlen($env_config->git_password ?? '') ){
			$password = $env_config->git_password;
		}
		if( !strlen($url ?? '') ){
			return null;
		}

		$parsed_git_url = parse_url($url);
		$rtn = '';
		$rtn .= $parsed_git_url['scheme'].'://';
		if( strlen($user_name ?? '') ){
			$rtn .= urlencode($user_name);
			if( strlen($password ?? '') ){
				$rtn .= ':'.urlencode($password);
			}
			$rtn .= '@';
		}
		$rtn .= $parsed_git_url['host'];
		if( array_key_exists('port', $parsed_git_url) && strlen($parsed_git_url['port'] ?? '') ){
			$rtn .= ':'.$parsed_git_url['port'];
		}
		$rtn .= $parsed_git_url['path'];
		if( array_key_exists('query', $parsed_git_url) && strlen($parsed_git_url['query'] ?? '') ){
			$rtn .= '?'.$parsed_git_url['query'];
		}
		return $rtn;
	}

	/**
	 * gitコマンドの結果から、秘匿な情報を隠蔽する
	 * @param string $str 出力テキスト
	 * @return string 秘匿情報を隠蔽加工したテキスト
	 */
	private function conceal_confidentials($str){

		// gitリモートリポジトリのURLに含まれるパスワードを隠蔽
		// ただし、アカウント名は残す。
		$str = preg_replace('/((?:[a-zA-Z\-\_]+))\:\/\/([^\s\/\\\\]*?\:)([^\s\/\\\\]*)\@/si', '$1://$2********@', $str);

		return $str;
	}
}
