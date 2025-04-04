<?php
namespace tomk79\onionSlice\api;

class scheduler {

	private $rencon;
	private $projects;
	private $project_id;
	private $scheduler;
	private $schedule_id;

	/**
	 * API: スケジューラーの配信タスクを取得する
	 */
	static public function api_get_scheduler_tasks( $rencon ){
		$ctrl = new self($rencon);
		return $ctrl->get_task_all();
	}

	/**
	 * API: ウェイターからタスクの処理結果のレポートを受け付ける
	 */
	static public function api_report_scheduler_task( $rencon ){
		$ctrl = new self($rencon);
		return $ctrl->report_scheduler_task(
			$rencon->req()->get_param('id'),
			$rencon->req()->get_param('result'),
			$rencon->req()->get_param('message')
		);
	}

	/**
	 * Console: スケジューラーの配信タスクを取得する
	 */
	static public function console_get_scheduler_tasks( $rencon ){
		$ctrl = new self($rencon);
		return json_encode($ctrl->get_task_all());
	}

	/**
	 * Console: ウェイターからタスクの処理結果のレポートを受け付ける
	 */
	static public function console_report_scheduler_task( $rencon ){
		$ctrl = new self($rencon);
		return json_encode($ctrl->report_scheduler_task(
			$rencon->req()->get_cli_option('--id'),
			$rencon->req()->get_cli_option('--result'),
			$rencon->req()->get_cli_option('--message')
		));
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
	 * 配信タスクの一覧を取得する
	 */
	public function get_task_all(){
		$rtn = (object) array();

		$tasks = $this->scheduler->get_task_all();

		$rtn->result = true;
		$rtn->message = "OK.";
		$rtn->tasks = $tasks;

		return $rtn;
	}

	/**
	 * 配信タスクの処理結果報告を受け付ける
	 */
	public function report_scheduler_task($task_id, $result, $message){
		$rtn = (object) array();

		$rtn->result = true;
		$rtn->message = "OK.";

		$this->scheduler->log_task($task_id, $result, $message);

		return $rtn;
	}

}
