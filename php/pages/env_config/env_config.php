<?php
namespace tomk79\onionSlice\pages\env_config;

class env_config {

	private $rencon;
	private $env_config;
	private $pickles2;

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
		$this->pickles2 = new \tomk79\onionSlice\helpers\pickles2( $this->rencon );
	}


	/**
	 * ルーティング
	 */
	private function route(){

		if( $this->rencon->req()->get_param('m') == 'completed' ){
			return $this->completed();
		}

		if( !strlen($this->rencon->req()->get_param('m')) ){
			$this->rencon->req()->set_param('url-preview', $this->env_config->url_preview);
			$this->rencon->req()->set_param('url-production', $this->env_config->url_production);
			$this->rencon->req()->set_param('git-url', $this->env_config->git_url);
			$this->rencon->req()->set_param('git-username', $this->env_config->git_username);
			$this->rencon->req()->set_param('git-password', $this->env_config->git_password);
		}

		if( $this->rencon->req()->get_param('m') == 'save' ){
			$this->save();
			exit;
		}

		return $this->edit();
	}


	/**
	 * 編集画面
	 */
	private function edit(){
		$px2proj = $this->pickles2->create_px2agent();
		$px2all = $px2proj->query(
			'/?PX=px2dthelper.get.all',
			array(
				'output' => 'json'
			)
		);
		$realpath_entry_script = $this->pickles2->get_entry_script();
		$realpath_publish_dir = $this->rencon->fs()->get_realpath('./'.$px2all->config->path_publish_dir, dirname($realpath_entry_script));

?>

<form action="?a=<?= htmlspecialchars($this->rencon->req()->get_param('a')) ?>" method="post">
	<input type="hidden" name="m" value="save" />

	<!-- ID/PWのオートコンプリートを無効にするためのダミー入力欄 -->
	<input type="password" name="autocomplete-off" value="" style="position: absolute; visibility: hidden; top: -100px; left: -100px;" />


	<div class="px2-form-input-list">
		<ul class="px2-form-input-list__ul">
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-url-preview">プレビューURL</label></div>
				<div class="px2-form-input-list__input">
					<input type="text" id="input-url-preview" name="input-url-preview" value="<?= htmlspecialchars($this->rencon->req()->get_param('url-preview')) ?>" class="px2-input px2-input--block" />
					<p>次のパスに割り当ててください。</p>
					<pre><code><?= htmlspecialchars( $px2all->realpath_docroot ) ?></code></pre>
				</div>
			</li>
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-url-production">本番URL</label></div>
				<div class="px2-form-input-list__input">
					<input type="text" id="input-url-production" name="input-url-production" value="<?= htmlspecialchars($this->rencon->req()->get_param('url-production')) ?>" class="px2-input px2-input--block" />
					<p>次のパスに割り当ててください。</p>
					<pre><code><?= htmlspecialchars( $realpath_publish_dir ) ?></code></pre>
				</div>
			</li>
		</ul>
	</div>

	<div class="px2-form-input-list">
		<ul class="px2-form-input-list__ul">
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-git-url">Gitリモート URL</label></div>
				<div class="px2-form-input-list__input"><input type="text" id="input-git-url" name="input-git-url" value="<?= htmlspecialchars($this->rencon->req()->get_param('git-url')) ?>" class="px2-input px2-input--block" /></div>
			</li>
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-git-username">Gitリモート ユーザー名</label></div>
				<div class="px2-form-input-list__input"><input type="text" id="input-git-username" name="input-git-username" value="<?= htmlspecialchars($this->rencon->req()->get_param('git-username')) ?>" class="px2-input px2-input--block" /></div>
			</li>
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-git-password">Gitリモート パスワード</label></div>
				<div class="px2-form-input-list__input">
					<input type="password" id="input-git-password" name="input-git-password" value="" class="px2-input px2-input--block" />
					<ul class="px2-note-list">
						<li>変更する場合のみ入力してください。</li>
					</ul>
				</div>
			</li>
		</ul>
	</div>

	<p class="px2-text-align-center"><button class="px2-btn px2-btn--primary">保存する</button></p>
</form>

<?php
		return;
	}


	/**
	 * 保存処理を実行する
	 */
	private function save(){
		$this->env_config->url_preview = $this->rencon->req()->get_param('input-url-preview');
		$this->env_config->url_production = $this->rencon->req()->get_param('input-url-production');
		$this->env_config->git_url = $this->rencon->req()->get_param('input-git-url');
		$this->env_config->git_username = $this->rencon->req()->get_param('input-git-username');
		if( strlen($this->rencon->req()->get_param('input-git-password')) ){
			$this->env_config->git_password = $this->rencon->req()->get_param('input-git-password');
		}
		$this->env_config->save();

		header("Location: ?a=".htmlspecialchars($this->rencon->req()->get_param('a')).'&m=completed');
		exit;
	}


	/**
	 * 完了画面
	 */
	private function completed(){
?>

<p>保存しました。</p>
<p><button class="px2-btn" onclick="window.location.href='?a=<?= htmlspecialchars($this->rencon->req()->get_param('a')) ?>';">完了</button></p>

<?php
		return;
	}

}
