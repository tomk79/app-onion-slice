<?php
namespace tomk79\onionSlice\api;

class scheduler {

	private $rencon;
	private $projects;
	private $project_id;
	private $scheduler;
	private $schedule_id;

	/**
	 * API: スケジュールを取得する
	 */
	static public function api_get_schedule( $rencon ){
		$ctrl = new self($rencon);
		return $ctrl->get_schedule();
	}

	/**
	 * Constructor
	 */
	public function __construct( $rencon ){
		$this->rencon = $rencon;
		$this->projects = new \tomk79\onionSlice\model\projects( $this->rencon );
		$this->project_id = $rencon->get_route_param('projectId');
		$this->scheduler = new \tomk79\onionSlice\model\scheduler( $this->rencon, $this->project_id );
		$this->schedule_id = $rencon->get_route_param('scheduleId');
	}

	/**
	 * スケジュールを取得する
	 */
	public function get_schedule(){
		$rtn = (object) array();

		$schedule = $this->scheduler->get_schedule_all();

		$rtn->result = true;
		$rtn->message = "OK.";
		$rtn->schedule = $schedule;

		return $rtn;
	}

}
