<?php
namespace tomk79\onionSlice\model;
use renconFramework\dataDotPhp;

class scheduler {

	private $rencon;
	private $project;
	private $realpath_project_data_dir;

	/**
	 * Constructor
	 */
	public function __construct( $rencon, $project_id ){
		$this->rencon = $rencon;

		$projects = new \tomk79\onionSlice\model\projects($this->rencon);
		$this->project = $projects->get( $project_id );

		$this->realpath_project_data_dir = $this->project->get_realpath_project_data_dir();
		if( is_string($this->realpath_project_data_dir) && is_dir($this->realpath_project_data_dir) && !is_dir($this->realpath_project_data_dir.'schedule/') ){
			$this->rencon->fs()->mkdir($this->realpath_project_data_dir.'schedule/');
		}
		return;
	}
}
