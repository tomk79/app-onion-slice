<?php
namespace tomk79\onionSlice\model;
use renconFramework\dataDotPhp;

class scheduler {

	private $rencon;
	private $project;

	/**
	 * Constructor
	 */
	public function __construct( $rencon, $project_id ){
		$this->rencon = $rencon;

        $projects = new \tomk79\onionSlice\model\projects($this->rencon);
		$this->project = $projects->get( $project_id );
		return;
	}
}
