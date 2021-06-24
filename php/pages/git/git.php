<?php
namespace tomk79\onionSlice\pages\git;

class git {

	private $rencon;

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
	}


	/**
	 * ルーティング
	 */
	private function route(){
		if( $this->rencon->req()->get_param('m') == 'git_cmd' ){
			return $this->git_cmd();
		}

		return $this->index();
	}


	/**
	 * インデックスページ
	 */
	public function index(){
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
		var method = 'post';
		var apiUrl = "?a=git&m=git_cmd";


		// --------------------------------------
		// gitui79 をセットアップ
		var $elm = document.querySelector('.cont-git');

		var gitUi79 = new GitUi79( $elm, function( cmdAry, callback ){
			var result = {};
			console.log('=-=-=-=-=-=-= GPI Request:', cmdAry, callback);

			$.ajax({
				"type" : method,
				"url" : apiUrl,
				"headers": {
				},
				"dataType": 'json',
				"data": {
					"command_ary": cmdAry
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
						callback(message.return, message.stdout+message.stderr);
						console.log(message.return, message.stdout+message.stderr);
					}catch(e){
						console.error(e);
						alert('Failed');
					}

					return;
				}
			});

		}, {
			committer: {
				name: "---",
				email: "---"
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
		$git_command_array = $this->rencon->req()->get_param('command_ary');
		$rtn = (object) array();


		$gitUtil = new \tomk79\onionSlice\helpers\git( $this->rencon );
		$gitUtil->set_remote_origin();

		$rtn = $gitUtil->git( $git_command_array );

		$gitUtil->clear_remote_origin();

		$rtn = (object) $rtn;


		// / --------------------------------------



		header('Content-type: text/json');
		echo json_encode($rtn);
		exit;
	}

}
