<?php
namespace tomk79\onionSlice;

class px2ctrl {
	private $rencon;


	/**
	 * Constructor
	 */
	public function __construct( $rencon ){
		$this->rencon = $rencon;
	}


	/**
	 * px2agent を生成する
	 */
	public function create_px2agent(){
		$path_entry_script = $this->get_entry_script();

		$px2agent = new \picklesFramework2\px2agent\px2agent(array(
			'bin' => $this->rencon->conf()->commands->php,
		));
		$px2proj = $px2agent->createProject( $this->rencon->conf()->path_data_dir.'/project/'.$path_entry_script );
		return $px2proj;
	}




	/**
	 * entryScriptのパスを調べる
	 */
	public function get_entry_script(){
		if( !$this->rencon->fs()->is_file($this->rencon->conf()->path_data_dir.'/project/composer.json') ){
			return false;
		}

		$path_entry_script = '.px_execute.php';

		$src_composer_json = $this->rencon->fs()->read_file( $this->rencon->conf()->path_data_dir.'/project/composer.json' );
		$composer_json = json_decode( $src_composer_json );

		if( !isset( $composer_json->extra->px2package ) ){
			return $path_entry_script;
		}

		if( is_object($composer_json->extra->px2package) && isset($composer_json->extra->px2package->path) ){
			$path_entry_script = $composer_json->extra->px2package->path;
		}elseif( is_array($composer_json->extra->px2package) ){
			foreach( $composer_json->extra->px2package as $row ){
				if( is_object($row) && isset($row->path) ){
					if( isset($row->type) && $row->type != 'project' ){
						continue;
					}
					$path_entry_script = $row->path;
					break;
				}
			}
		}

		return $path_entry_script;
	}

}
