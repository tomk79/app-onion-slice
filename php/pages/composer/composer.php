<?php
namespace tomk79\onionSlice\pages\composer;

class composer {

	private $rencon;

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

		$this->projects = new \tomk79\onionSlice\model\projects($rencon);
		$this->project_id = $rencon->get_route_param('projectId');
		$this->project_info = $this->projects->get_project($this->project_id);
	}


	/**
	 * ルーティング
	 */
	private function route(){
		if( $this->rencon->req()->get_param('m') == 'composer_cmd' ){
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
				'ADMIN_USER_CSRF_TOKEN': $('meta[name="csrf-token"]').attr('content'),
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
		$command = $this->rencon->req()->get_param('command');
		$rtn = (object) array();

		if( !$this->project_info ) {
			header('Content-type: text/json');
			$rtn->result = false;
			$rtn->message = "Project is not defined.";
			echo json_encode($rtn);
			exit;
		}

		$path_composer = $this->rencon->fs()->get_realpath($this->rencon->conf()->realpath_private_data_dir.'commands/composer/composer.phar');
		if( !is_file($path_composer) ){
			$this->rencon->fs()->mkdir_r( dirname($path_composer) );
			$this->rencon->fs()->save_file( $path_composer, $this->rencon->resources()->get('resources/composer.phar') );
		}
		$base_dir = $this->project_info->realpath_base_dir;
		$current_dir = realpath('.');

		if( !is_dir($base_dir) ) {
			header('Content-type: text/json');
			$rtn->result = false;
			$rtn->message = "Project base dir is not exists.";
			echo json_encode($rtn);
			exit;
		}

		$rtn->command = $this->rencon->conf()->commands->php.' '.$path_composer.' '.$command;

		chdir($base_dir);

		ob_start();
		$proc = proc_open($rtn->command, array(
			0 => array('pipe','r'),
			1 => array('pipe','w'),
			2 => array('pipe','w'),
		), $pipes);

		$io = array();
		foreach($pipes as $idx=>$pipe){
			if($idx){
				$io[$idx] = stream_get_contents($pipe);
			}
			fclose($pipe);
		}
		$return_var = proc_close($proc);
		ob_get_clean();

		chdir($current_dir);


		$rtn->result = true;
		$rtn->exit = $return_var;
		$rtn->stdout = $io[1];
		$rtn->stderr = $io[2];

		header('Content-type: text/json');
		echo json_encode($rtn);
		exit;
	}

}
