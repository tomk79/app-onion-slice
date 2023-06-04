<?php
namespace tomk79\onionSlice\pages\contents_editor;

class contents_editor {

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
		if( $this->rencon->req()->get_param('m') == 'res' ){
			$this->res();
			exit;
		}elseif( $this->rencon->req()->get_param('m') == 'px2ce_gpi' ){
			$this->px2ce_gpi();
			exit;
		}
		return $this->index();
	}


	/**
	 * 編集画面
	 */
	private function index(){
		$env_config = new \tomk79\onionSlice\model\env_config( $this->rencon );

		$page_path = $this->rencon->req()->get_param('page_path');
		$theme_id = $this->rencon->req()->get_param('theme_id');
		$layout_id = $this->rencon->req()->get_param('layout_id');

		$client_resources_dist = $this->rencon->conf()->path_data_dir.'/temporary_assets/px2ce_resources/';
		$client_resources_dist = $this->rencon->fs()->get_realpath( $client_resources_dist );

		if( !is_dir($client_resources_dist) ){
			$this->rencon->fs()->mkdir_r($client_resources_dist);
		}

		$pickles2 = new \tomk79\onionSlice\helpers\pickles2($this->rencon);
		$px2proj = $pickles2->create_px2agent();
		$px2ce_client_resources = $px2proj->query(
			'/?PX=px2dthelper.px2ce.client_resources&dist='.urlencode($client_resources_dist),
			array(
				"output" => "json",
			)
		);

		$relative_path = $this->rencon->fs()->get_relatedpath( $client_resources_dist );
		foreach($px2ce_client_resources->css as $value) {
			echo '<link href="'.$relative_path.$value.'" rel="stylesheet" />'."\n";
		}
		foreach($px2ce_client_resources->js as $value) {
			echo '<script src="'.$relative_path.$value.'"></script>'."\n";
		}


?>

<div id="canvas" style="flex-grow: 100; height:100%;"></div>


<!-- Ace Editor -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.4/ace.js"></script>

<!-- Keypress -->
<script src="?res=common/dmauro-Keypress/keypress.js"></script>

<script type="text/javascript">
	(function(){
		var page_path = <?= json_encode($page_path, JSON_UNESCAPED_SLASHES); ?>;
		var theme_id = <?= json_encode($theme_id, JSON_UNESCAPED_SLASHES); ?>;
		var layout_id = <?= json_encode($layout_id, JSON_UNESCAPED_SLASHES); ?>;
		var target_mode = 'page_content';
		var preview_url = <?= json_encode($env_config->url_preview, JSON_UNESCAPED_SLASHES) ?>;
		var resizeTimer;

		if( page_path ){
			target_mode = 'page_content';
		}else if( theme_id && layout_id ){
			target_mode = 'theme_layout';
			page_path = '/'+theme_id+'/'+layout_id+'.html';
		}

		// Px2CE の初期化
		var pickles2ContentsEditor = new Pickles2ContentsEditor(); // px2ce client
		pickles2ContentsEditor.init(
			{
				// いろんな設定値
				// これについては Px2CE の README を参照
				// https://github.com/pickles2/node-pickles2-contents-editor
				'page_path': page_path , // <- 編集対象ページのパス
				'elmCanvas': document.getElementById('canvas'), // <- 編集画面を描画するための器となる要素
				'preview':{
					'origin': preview_url,
				},
				'lang': 'ja', // language
				'gpiBridge': function(input, callback){
					console.log('====== GPI Request:', input);
					console.log(JSON.stringify(input));
					$.ajax({
						"url": '?a='+<?= var_export($this->rencon->req()->get_param('a'), true) ?>+'&m=px2ce_gpi&page_path='+page_path+'&target_mode='+target_mode, // ←呼び出し元が決める
						"method": 'post',
						'data': {
							'data':JSON.stringify(input),
				            'ADMIN_USER_CSRF_TOKEN': $('meta[name="csrf-token"]').attr('content'),
						},
						"error": function(error){
							console.error('------ GPI Response Error:', typeof(error), error);
							callback(data.res);
						},
						"success": function(data){
							console.log('------ GPI Response:', typeof(data), data);
							callback(data.res);
						}
					});
					return;
				},
				'complete': function(){
					window.open('about:blank', '_self').close();
				},
				'clipboard': {
					'set': function( data, type, event, callback ){
						// console.log(data, type, event, callback);
						localStorage.setItem('app-burdock-virtualClipBoard', data);
						if( callback ){
							callback();
						}
					},
					'get': function( type, event, callback ){
						var rtn = localStorage.getItem('app-burdock-virtualClipBoard');
						// console.log(rtn);
						if( callback ){
							callback(rtn);
							return false;
						}
						return rtn;
					}
				},
				'onClickContentsLink': function( uri, data ){
					// TODO: 編集リンクを生成する
					// alert('編集: ' + uri);
				},
				'onMessage': function( message ){
					// ユーザーへ知らせるメッセージを表示する
					px2style.flashMessage(message);
					console.info('message: '+message);
				}
			},
			function(){
				// コールバック
				// 初期化完了！
				console.info('Standby!');

			}
		);

		$(window).on('resize', function(){
			clearTimeout(resizeTimer);
			resizeTimer = setTimeout(function(){
				if(pickles2ContentsEditor.redraw){
					pickles2ContentsEditor.redraw();
				}
			}, 500);
			return;
		});
	})();
</script>



<?php
		return;
	}


	/**
	 * リソースを出力
	 */
	private function res(){
		$client_resources_dist = $this->rencon->conf()->path_data_dir.'/temporary_assets/px2ce_resources/';
		$res_path = $this->rencon->req()->get_param('path');
		$bin = '';
		if( !is_file($client_resources_dist.$res_path) ){
			header('Content-type: text/html');
			echo '404 Not Found.';
			exit;
		}
		$bin = file_get_contents( $client_resources_dist.$res_path );

		$ext = preg_replace('/^.*\.([a-z0-9]*?)$/i', '$1', $res_path);
		$mime = 'text/html';
		switch( $ext ){
			case 'js':
				$mime = 'text/javascript'; break;
			case 'css':
				$mime = 'text/css'; break;
			case 'gif':
				$mime = 'image/gif'; break;
			case 'png':
				$mime = 'image/png'; break;
			case 'jpg':
			case 'jpeg':
			case 'jpe':
				$mime = 'image/jpeg'; break;
		}

		header('Content-type: '.$mime);
		echo $bin;
		exit;
	}

	/**
	 * Pickles 2 Contents Editor の GPI
	 */
	private function px2ce_gpi()
	{

		$pickles2 = new \tomk79\onionSlice\helpers\pickles2($this->rencon);
		$px2proj = $pickles2->create_px2agent();
		$current = $px2proj->query(
			'/?PX=px2dthelper.get.all',
			array(
				"output" => "json",
			)
		);


		// ミリ秒を含むUnixタイムスタンプを数値（Float）で取得
		$timestamp = microtime(true);
		// ミリ秒とそうでない部分を分割
		$timeInfo = explode('.', $timestamp);
		// ミリ秒でない時間の部分を指定のフォーマットに変換し、その末尾にミリ秒を追加
		$timeWithMillisec = date('YmdHis', $timeInfo[0]).$timeInfo[1];
		// 一時ファイル名を作成
		$tmpFileName = '__tmp_'.md5($timeWithMillisec).'_data.json';
		// 一時ファイルを保存
		$file = $current->realpath_homedir.'_sys/ram/data/'.$tmpFileName;
		file_put_contents($file, $this->rencon->req()->get_param('data'));

		$page_path = $this->rencon->req()->get_param('page_path');
		$target_mode = $this->rencon->req()->get_param('target_mode');

		$result = $px2proj->query(
			$page_path.'?PX=px2dthelper.px2ce.gpi&data_filename='.urlencode($tmpFileName).'&target_mode='.urlencode($target_mode),
			array(
				"output" => "json",
			)
		);

		// 作成した一時ファイルを削除
		unlink($file);


		$rtn = array();
		$rtn['result'] = true;
		$rtn['res'] = $result;

		header('Content-type: text/json');
		echo json_encode($rtn);
		exit;
	}

}
