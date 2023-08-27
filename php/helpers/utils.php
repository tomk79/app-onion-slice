<?php
namespace tomk79\onionSlice\helpers;

/**
 * Utility
 */
class utils {

	/** $renconオブジェクト */
	private $rencon;

	/**
	 * Constructor
	 *
	 * @param object $rencon $renconオブジェクト
	 */
	public function __construct( $rencon ){
		$this->rencon = $rencon;
	}

	/**
	 * method が post 以外だったら、 405 エラーのJSONを出力する
	 */
	public function api_post_only(){
		if( $this->rencon->req()->get_method() != 'post' ){
			header('Content-type: text/json');
			header("HTTP/1.0 405 Method Not Allowed");
			echo json_encode(array(
				"result" => false,
				"message" => "Method Not Allowed",
			));
			exit;
		}
	}

	/**
	 * プロジェクトのベースディレクトリが存在するか？
	 */
	public function base_dir_exists( $project_id = null ){
		$projects = new \tomk79\onionSlice\model\projects($this->rencon);
		if( !strlen($project_id ?? '') ){
			$project_id = $this->rencon->get_route_param('projectId');
		}
		if( !strlen($project_id ?? '') ){
			return false;
		}
		$project_info = $projects->get_project($project_id);

		if( !strlen($project_info->realpath_base_dir ?? '') ){
			return false;
		}
		if( !is_dir($project_info->realpath_base_dir ?? null) ){
			return false;
		}
		return true;
	}


	/**
	 * プロジェクトが composer.json を配置しているか？
	 */
	public function has_composer_json( $project_id = null ){
		$projects = new \tomk79\onionSlice\model\projects($this->rencon);
		if( !strlen($project_id ?? '') ){
			$project_id = $this->rencon->get_route_param('projectId');
		}
		if( !strlen($project_id ?? '') ){
			return false;
		}
		$project_info = $projects->get_project($project_id);

		if( !strlen($project_info->realpath_base_dir ?? '') ){
			return false;
		}
		if( !is_dir($project_info->realpath_base_dir ?? null) ){
			return false;
		}
		if( !is_file($project_info->realpath_base_dir.'/composer.json') ){
			return false;
		}
		return true;
	}

	/**
	 * プロジェクトが .git を配置しているか？
	 */
	public function has_dot_git( $project_id = null ){
		$projects = new \tomk79\onionSlice\model\projects($this->rencon);
		if( !strlen($project_id ?? '') ){
			$project_id = $this->rencon->get_route_param('projectId');
		}
		if( !strlen($project_id ?? '') ){
			return false;
		}
		$project_info = $projects->get_project($project_id);

		if( !strlen($project_info->realpath_base_dir ?? '') ){
			return false;
		}
		if( !is_dir($project_info->realpath_base_dir ?? null) ){
			return false;
		}
		if( !file_exists($project_info->realpath_base_dir.'/.git') ){
			return false;
		}
		return true;
	}
}
