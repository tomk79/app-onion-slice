<?php
namespace tomk79\onionSlice\pages\git;

class git {

	private $rencon;
	private $profile;

	private $projects;
	private $project_id;
	private $project_info;

	/**
	 * 処理の開始
	 */
	static public function start( $rencon ){
		$ctrl = new self($rencon);
		return $ctrl->route();
	}

	/**
	 * Constructor
	 */
	public function __construct( $rencon ){
		$this->rencon = $rencon;

		$this->profile = new \tomk79\onionSlice\model\profile( $this->rencon );

		$this->projects = new \tomk79\onionSlice\model\projects($rencon);
		$this->project_id = $rencon->get_route_param('projectId');
		$this->project_info = $this->projects->get_project($this->project_id);
	}


	/**
	 * ルーティング
	 */
	private function route(){
		if( !strlen($this->project_info->realpath_base_dir ?? '') || !is_dir($this->project_info->realpath_base_dir) ){
			echo '<p>ベースディレクトリが存在しないか、設定されていません。</p>';
			return;
		}

		if( $this->rencon->req()->get_param('m') == 'git_cmd' ){
			$this->rencon->utils->api_post_only();
			return $this->git_cmd();
		}

		return $this->index();
	}


	/**
	 * インデックスページ
	 */
	public function index(){
		$profile = $this->profile->get();
?>
<div class="cont-git"></div>


<link rel="stylesheet" href="?res=gitui79.js/gitui79.css" />
<style>
.theme-frame{
	display: flex;
	flex-direction: column;

}
.theme-frame .theme-main-container {
    flex-grow: 100;
    flex-shrink: 0;

    display: flex;
    flex-direction: column;
    height: 100px;
}

.theme-frame .theme-main-container .theme-main-container__body,
.theme-frame .theme-main-container .contents {
    flex-grow: 100;
    overflow: auto;

    display: flex;
    flex-direction: column;

}
.theme-frame .theme-main-container .theme-main-container__body .cont-git,
.theme-frame .theme-main-container .contents .cont-git {
    height: 100%;
}
</style>
<script src="?res=gitui79.js/gitui79.js"></script>

<script>
window.contApp = new (function(){
	var _this = this;
	var $cont;

	/**
	 * initialize
	 */
	function init(){
		$cont = $('.cont-git').html('');
		var apiUrl = "?a=proj.<?= urlencode($this->project_id ?? '') ?>.git&m=git_cmd";


		// --------------------------------------
		// gitui79 をセットアップ
		var $elm = document.querySelector('.cont-git');

		var gitUi79 = new GitUi79( $elm, function( cmdAry, callback ){
			var result = {};
			console.log('=-=-=-=-=-=-= GPI Request:', cmdAry, callback);

			$.ajax({
				"type" : 'post',
				"url" : apiUrl,
				"headers": {
				},
				"dataType": 'json',
				"data": {
					"command_ary": JSON.stringify(cmdAry),
		            'CSRF_TOKEN': $('meta[name="csrf-token"]').attr('content'),
				},
				"error": function(data){
					result = data;
					console.error('error', data);
				},
				"success": function(data){
					result = data;
					console.log(data);
				},
				"complete": function(){
					console.log('------ GPI Request complete:', result);
					let message = result;

					try{
						callback(message.exitcode, (message.stdout?message.stdout:'')+(message.stderr?message.stderr:''));
						console.log(message.return, message.stdout+message.stderr);
					}catch(e){
						console.error(e);
						alert('Failed');
					}

					return;
				}
			});

		}, {
			"lang": <?= json_encode( $profile->lang ) ?>,
			"committer": {
				"name": <?= json_encode( $profile->name ) ?>,
				"email": <?= json_encode( $profile->email ) ?>,
			}
		} );

		gitUi79.init(function(){
			console.log('gitUi79: Standby.');
		});

	}

	/**
	 * イベント
	 */
	window.addEventListener('load', function(e){
		init();
	});

})();

</script>


<?php
		return;
	}


	/**
	 * git command
	 */
	public function git_cmd(){
		$git_command_array = json_decode( $this->rencon->req()->get_param('command_ary') );
		$rtn = (object) array();

		if( !$this->project_info ) {
			header('Content-type: text/json');
			$rtn->result = false;
			$rtn->message = "Project is not defined.";
			echo json_encode($rtn);
			exit;
		}

		$base_dir = $this->project_info->realpath_base_dir;

		if( !is_dir($base_dir) ) {
			header('Content-type: text/json');
			$rtn->result = false;
			$rtn->message = "Project base dir is not exists.";
			echo json_encode($rtn);
			exit;
		}

		$gitUtil = new \tomk79\onionSlice\helpers\git( $this->rencon, $this->project_info );
		$gitUtil->set_remote_origin($this->project_info->remote);

		$rtn = $gitUtil->git( $git_command_array );

		$gitUtil->clear_remote_origin();

		$rtn = (object) $rtn;


		// / --------------------------------------



		header('Content-type: text/json');
		echo json_encode($rtn);
		exit;
	}

}
