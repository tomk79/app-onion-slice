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

}
