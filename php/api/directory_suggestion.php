<?php
namespace tomk79\onionSlice\api;

class directory_suggestion {

	/**
	 * 入力中のパスから、候補を提案する
	 */
	static public function suggest( $rencon ){
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

		$realpath_base_dir = $rencon->req()->get_param('realpath_base_dir');
		if( !strlen($realpath_base_dir ?? '') ){
			$rtn->result = false;
			$rtn->message = "realpath_base_dir is required.";
			echo json_encode($rtn);
			exit;
		}

		$rtn->current_dir = $realpath_base_dir;
		$rtn->basename = null;
		$rtn->suggestion = array();
		if( !is_dir($rtn->current_dir) ){
			$rtn->current_dir = dirname($realpath_base_dir);
			$rtn->basename = basename($realpath_base_dir);
		}

		if( is_dir($rtn->current_dir) ){
			$ls = $rencon->fs()->ls($rtn->current_dir);
			foreach($ls as $filename){
				if( strlen($rtn->basename ?? '') && strpos($filename, $rtn->basename) !== 0 ){
					continue;
				}
				$row_realpath = $rencon->fs()->get_realpath($rtn->current_dir.'/'.$filename);
				array_push($rtn->suggestion, array(
					"type" => (is_dir($row_realpath) ? 'directory' : ( is_file($row_realpath) ? 'file' : 'unknown' )),
					"realpath" => $row_realpath.((is_dir($row_realpath) ? '/' : '')),
				));
			}
		}

		echo json_encode($rtn);
		exit;
	}

}
