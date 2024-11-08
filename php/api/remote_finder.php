<?php
namespace tomk79\onionSlice\api;

class remote_finder {

	/**
	 * Remote Finder: GPI
	 */
	static public function gpi( $rencon ){
		$projects = new \tomk79\onionSlice\model\projects($rencon);
		$project_id = $rencon->get_route_param('projectId');
		$project_info = $projects->get_project($project_id);

		if( $rencon->req()->get_method() != 'post' ){
			header('Content-type: text/json');
			$rtn->result = false;
			$rtn->message = "Method not allowed.";
			echo json_encode($rtn);
			exit;
		}

		if( !$project_info ) {
			header('Content-type: text/json');
			$rtn->result = false;
			$rtn->message = "Project is not defined.";
			echo json_encode($rtn);
			exit;
		}

		$base_dir = $project_info->realpath_base_dir;

		if( !is_dir($base_dir) ) {
			header('Content-type: text/json');
			$rtn->result = false;
			$rtn->message = "Project base dir is not exists.";
			echo json_encode($rtn);
			exit;
		}

		$remoteFinder = new \tomk79\remoteFinder\main(array(
			'default' => $base_dir,
		), array(
			'paths_invisible' => array(
				// '/invisibles/*',
				// '*.hide'
			),
			'paths_readonly' => array(
				'/.git/*',
				'/vendor/*',
				'/node_modules/*',
			),
		));

		$value = $remoteFinder->gpi( json_decode( $rencon->req()->get_param('gpi_param') ) );

		header('Content-type: text/json');
		echo json_encode($value);
		exit;
	}

	/**
	 * Remote Finder: Download
	 */
	static public function download( $rencon ){
		$projects = new \tomk79\onionSlice\model\projects($rencon);
		$project_id = $rencon->get_route_param('projectId');
		$project_info = $projects->get_project($project_id);

		if( !$project_info ) {
			header('Content-type: text/json');
			$rtn->result = false;
			$rtn->message = "Project is not defined.";
			echo json_encode($rtn);
			exit;
		}

		$base_dir = $project_info->realpath_base_dir;

		if( !is_dir($base_dir) ) {
			header('Content-type: text/json');
			$rtn->result = false;
			$rtn->message = "Project base dir is not exists.";
			echo json_encode($rtn);
			exit;
		}

		$remoteFinder = new \tomk79\remoteFinder\main(array(
			'default' => $base_dir,
		), array(
			'paths_invisible' => array(
				// '/invisibles/*',
				// '*.hide'
			),
			'paths_readonly' => array(
				'/.git/*',
				'/vendor/*',
				'/node_modules/*',
			),
		));

		$remoteFinder->download( $rencon->req()->get_param('path') );
		exit;
	}

}
