<?php
namespace tomk79\onionSlice\pages\composer;

class composer {

	private $rencon;
	private $env_config;

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
		$this->env_config = new \tomk79\onionSlice\model\env_config( $this->rencon );

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

		if( $this->rencon->req()->get_param('m') == 'composer_cmd' ){
			$this->rencon->utils->api_post_only();
			return $this->composer_cmd();
		}

		return $this->index();
	}


	/**
	 * インデックスページ
	 */
	public function index(){
?>


<div class="cont-composer-btns">
	<button class="px2-btn" data-command="install">install</button>
	<button class="px2-btn" data-command="update">update</button>
</div>
<div class="cont-console">
	<pre><code></code></pre>
</div>



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

<script>
/**
 * イベント
 */
window.addEventListener('load', function(e){
	let $btn = $('.cont-composer-btns button');
	let $console = $('.cont-console pre code');

	$btn.on('click', function(){
		let command = $(this).attr('data-command');

		$btn.attr({'disabled': true});
		px2style.loading();

		$console.html('');

		$.ajax({
			"url": '?a=<?= urlencode( $this->rencon->req()->get_param('a') ) ?>',
			'method': 'post',
			'data': {
				'm': 'composer_cmd',
				'command': command,
				'CSRF_TOKEN': $('meta[name="csrf-token"]').attr('content'),
			},
			'success': function(data){
				console.log(data);
				if( data.result ){
					$console.html(data.stdout + data.stderr);
				}
			},
			'error': function(data){
				console.error(data);
				if( data.result ){
					$console.html(data.stdout + data.stderr);
				}
			},
			'complete': function(){
				console.log('done!');
				$btn.attr({'disabled': false});
				px2style.closeLoading();
			}
		});

	});
});

</script>


<?php
		return;
	}


	/**
	 * composer command
	 */
	public function composer_cmd(){
		$composerHelper = new \tomk79\onionSlice\helpers\composer( $this->rencon, $this->project_info );
		$rtn = $composerHelper->composer( array($this->rencon->req()->get_param('command')) );

		header('Content-type: text/json');
		echo json_encode($rtn);
		exit;
	}

}
