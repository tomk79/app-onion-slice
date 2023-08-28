<?php
namespace tomk79\onionSlice\api;

class initialize_project {

	/**
	 * 空のベースディレクトリを作成する
	 */
	static public function mk_empty_base_dir( $rencon ){
		header('Content-type: text/json');
		$rtn = (object) array(
			"result" => true,
			"message" => "OK",
		);

		if( $rencon->req()->get_method() != 'post' ){
			$rtn->result = false;
			$rtn->message = "Method not allowed.";
			echo json_encode($rtn);
			exit;
		}

		$projects = new \tomk79\onionSlice\model\projects($rencon);
		$project_id = $rencon->get_route_param('projectId');
		$project_info = $projects->get_project($project_id);

        if( !strlen($project_info->realpath_base_dir ?? '') ){
			$rtn->result = false;
			$rtn->message = "realpath_base_dir is not set.";
			echo json_encode($rtn);
			exit;
        }

        if( file_exists($project_info->realpath_base_dir) ){
			$rtn->result = false;
			$rtn->message = "realpath_base_dir is already exists.";
			echo json_encode($rtn);
			exit;
        }

        if( !$rencon->fs()->mkdir_r($project_info->realpath_base_dir) ){
			$rtn->result = false;
			$rtn->message = "Failed to make base directory.";
			echo json_encode($rtn);
			exit;
        }

		echo json_encode($rtn);
		exit;
	}

	/**
	 * git remote で初期化する
	 */
	static public function initialize_with_git_remote( $rencon ){
		header('Content-type: text/json');
		$rtn = (object) array(
			"result" => true,
			"message" => "OK",
		);

		if( $rencon->req()->get_method() != 'post' ){
			$rtn->result = false;
			$rtn->message = "Method not allowed.";
			echo json_encode($rtn);
			exit;
		}

		$projects = new \tomk79\onionSlice\model\projects($rencon);
		$project_id = $rencon->get_route_param('projectId');
		$project_info = $projects->get_project($project_id);
		$project = $projects->get($project_id);
		$realpath_git_root = $project_info->realpath_base_dir;

        if( !strlen($realpath_git_root ?? '') ){
			$rtn->result = false;
			$rtn->message = "realpath_base_dir is not set.";
			echo json_encode($rtn);
			exit;
        }

        if( !file_exists($realpath_git_root) ){
			$rtn->result = false;
			$rtn->message = "realpath_base_dir is not exists.";
			echo json_encode($rtn);
			exit;
        }

        if( !$project->is_project_base_dir_empty() ){
			$rtn->result = false;
			$rtn->message = "realpath_base_dir is not empty.";
			echo json_encode($rtn);
			exit;
        }

		// git clone を実装する
		$gitHelper = new \tomk79\onionSlice\helpers\git($rencon, $project_info);
		$result = $gitHelper->git_clone();
		$rtn = (object) $result;

		echo json_encode($rtn);
		exit;
	}

	/**
	 * Pickles 2 で初期化する
	 */
	static public function initialize_with_pickles2( $rencon ){
		header('Content-type: text/json');
		$rtn = (object) array(
			"result" => true,
			"message" => "OK",
		);

		if( $rencon->req()->get_method() != 'post' ){
			$rtn->result = false;
			$rtn->message = "Method not allowed.";
			echo json_encode($rtn);
			exit;
		}

		$projects = new \tomk79\onionSlice\model\projects($rencon);
		$project_id = $rencon->get_route_param('projectId');
		$project_info = $projects->get_project($project_id);
		$project = $projects->get($project_id);
		$realpath_git_root = $project_info->realpath_base_dir;

        if( !strlen($realpath_git_root ?? '') ){
			$rtn->result = false;
			$rtn->message = "realpath_base_dir is not set.";
			echo json_encode($rtn);
			exit;
        }

        if( !file_exists($realpath_git_root) ){
			$rtn->result = false;
			$rtn->message = "realpath_base_dir is not exists.";
			echo json_encode($rtn);
			exit;
        }

        if( !$project->is_project_base_dir_empty() ){
			$rtn->result = false;
			$rtn->message = "realpath_base_dir is not empty.";
			echo json_encode($rtn);
			exit;
        }

		// composer create-project を実装する
		$composerHelper = new \tomk79\onionSlice\helpers\composer( $rencon, $project_info );
		$rtn = $composerHelper->composer( array('create-project', 'pickles2/pickles2', './') );

		echo json_encode($rtn);
		exit;
	}

	/**
	 * git init する
	 */
	static public function git_init( $rencon ){
		header('Content-type: text/json');
		$rtn = (object) array(
			"result" => true,
			"message" => "OK",
		);

		if( $rencon->req()->get_method() != 'post' ){
			$rtn->result = false;
			$rtn->message = "Method not allowed.";
			echo json_encode($rtn);
			exit;
		}

		$projects = new \tomk79\onionSlice\model\projects($rencon);
		$project_id = $rencon->get_route_param('projectId');
		$project_info = $projects->get_project($project_id);
		$project = $projects->get($project_id);
		$realpath_git_root = $project_info->realpath_base_dir;

        if( !strlen($realpath_git_root ?? '') ){
			$rtn->result = false;
			$rtn->message = "realpath_base_dir is not set.";
			echo json_encode($rtn);
			exit;
        }

        if( !file_exists($realpath_git_root) ){
			$rtn->result = false;
			$rtn->message = "realpath_base_dir is not exists.";
			echo json_encode($rtn);
			exit;
        }

        if( $project->has_dot_git() ){
			$rtn->result = false;
			$rtn->message = "git init is already done.";
			echo json_encode($rtn);
			exit;
        }

		// git clone を実装する
		$gitHelper = new \tomk79\onionSlice\helpers\git($rencon, $project_info);
		$result = $gitHelper->git_init();
		$rtn = (object) $result;

		echo json_encode($rtn);
		exit;
	}

}
