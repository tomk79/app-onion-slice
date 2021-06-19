<?php
namespace tomk79\onionSlice\api;

class remote_finder {

	/**
	 * Pickles 2 の `PX=px2dthelper.get.all` を実行する
	 */
	static public function gpi( $rencon ){
		$remoteFinder = new \tomk79\remoteFinder\main(array(
			'default' => $rencon->conf()->path_data_dir.'/project/'
		), array(
			'paths_invisible' => array(
				// '/invisibles/*',
				// '*.hide'
			),
			'paths_readonly' => array(
				'/.git/*',
				'/vendor/*',
			),
		));

		$value = $remoteFinder->gpi( json_decode( $rencon->req()->get_param('gpi_param') ) );

		header('Content-type: text/json');
		echo json_encode($value);
		exit;
	}


	/**
	 * parse_px2_filepath
	 */
	static public function parse_px2_filepath( $rencon ){
		header('Content-type: text/json');

		$fs = $rencon->fs();
		$rtn = array();
		$pxExternalPath = $rencon->req()->get_param('path');
		$pxExternalPath = preg_replace( '/^\/*/', '', $pxExternalPath );

		$realpath_basedir = $rencon->conf()->path_data_dir.'project/';
		$realpath_file = $fs->normalize_path($fs->get_realpath($realpath_basedir.$pxExternalPath));

		$is_file = is_file($realpath_file);

		$px2ctrl = new \tomk79\onionSlice\px2ctrl($rencon);
		$px2proj = $px2ctrl->create_px2agent();

		$pageInfoAll = $px2proj->query(
			'/?PX=px2dthelper.get.all',
			array(
				'output' => 'json'
			)
		);
		// $rtn['pageInfoAll'] = $pageInfoAll;


		// --------------------------------------
		// 外部パスを求める
		if( is_object($pageInfoAll) && preg_match( '/^'.preg_quote($pageInfoAll->realpath_docroot, '/').'/', $realpath_file) ){
			$pxExternalPath = preg_replace('/^'.preg_quote($pageInfoAll->realpath_docroot, '/').'/', '/', $realpath_file);
			$pxExternalPath = preg_replace('/\/+/', '/', $pxExternalPath);
			if( preg_match( '/^'.preg_quote($pageInfoAll->path_controot, '/').'/', $pxExternalPath) ){
				$pxExternalPath = preg_replace('/^'.preg_quote($pageInfoAll->path_controot, '/').'/', '/', $pxExternalPath);
				$pxExternalPath = preg_replace('/\/+/', '/', $pxExternalPath);
			}else{
				$pxExternalPath = false;
			}
		}else{
			$pxExternalPath = false;
		}
		$rtn['pxExternalPath'] = $pxExternalPath;


		// --------------------------------------
		// パスの種類を求める
		// theme_collection, home_dir, contents, or unknown
		$path_type = 'unknown';
		if( is_object($pageInfoAll) ){
			$realpath_target = $fs->normalize_path($realpath_file);
			$realpath_homedir = $fs->normalize_path($pageInfoAll->realpath_homedir);
			$realpath_theme_collection_dir = $fs->normalize_path($pageInfoAll->realpath_theme_collection_dir);
			$realpath_docroot = $fs->normalize_path($pageInfoAll->realpath_docroot);
			if( preg_match('/^'.preg_quote($realpath_theme_collection_dir, '/').'/', $realpath_target) ){
				$path_type = 'theme_collection';
			}elseif( preg_match('/^'.preg_quote($realpath_homedir, '/').'/', $realpath_target) ){
				$path_type = 'home_dir';
			}elseif( preg_match('/^'.preg_quote($realpath_docroot, '/').'/', $realpath_target)  && $pxExternalPath ){
				$path_type = 'contents';
			}
			$rtn['pathType'] = $path_type;
		}

		$rtn['pathFiles'] = false;
		if( $rtn['pxExternalPath'] && $rtn['pathType'] == 'contents' ){
			$pageInfoAll = $px2proj->query(
				$rtn['pxExternalPath'].'?PX=px2dthelper.get.all',
				array(
					'output' => 'json'
				)
			);
			$realpath_files = $pageInfoAll->realpath_files;
			$realpath_basedir = $rencon->conf()->path_data_dir.'project/';
			$path_files = preg_replace('/^'.preg_quote($realpath_basedir, '/').'/', '/', $realpath_files);
			$rtn['pathFiles'] = $path_files;
		}

		echo json_encode($rtn);
		exit();
	}

}
