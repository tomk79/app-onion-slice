<?php
namespace tomk79\onionSlice\pages\env_config;

class env_config {

	private $rencon;
	private $env_config;

	/**
	 * 開始開始
	 */
	static public function index( $rencon ){
		$ctrl = new self($rencon);
		return $ctrl->index_route();
	}

	/**
	 * 編集画面
	 */
	static public function edit( $rencon ){
		$ctrl = new self($rencon);
		return $ctrl->edit_route();
	}

	/**
	 * Constructor
	 */
	public function __construct( $rencon ){
		$this->rencon = $rencon;
		$this->env_config = new \tomk79\onionSlice\model\env_config( $this->rencon );
	}


	/**
	 * 設定トップ画面: ルーティング
	 */
	private function index_route(){
?>

	<div class="px2-form-input-list">
		<ul class="px2-form-input-list__ul">
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-command-php">PHPコマンド</label></div>
				<div class="px2-form-input-list__input">
					<?= htmlspecialchars($this->env_config->commands->php ?? '') ?>
				</div>
			</li>
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-command-git">Gitコマンド</label></div>
				<div class="px2-form-input-list__input">
					<?= htmlspecialchars($this->env_config->commands->git ?? '') ?>
				</div>
			</li>
		</ul>
	</div>

	<p class="px2-text-align-center"><a href="?a=env_config.edit" class="px2-btn px2-btn--primary">編集する</a></p>

<?php
		return;
	}

	/**
	 * 編集画面: ルーティング
	 */
	private function edit_route(){

		if( $this->rencon->req()->get_param('m') == 'completed' ){
			return $this->edit_view_completed();
		}

		if( !strlen($this->rencon->req()->get_param('m') ?? '') ){
			$this->rencon->req()->set_param('command-php', $this->env_config->commands->php ?? null);
			$this->rencon->req()->set_param('command-git', $this->env_config->commands->git ?? null);
			// $this->rencon->req()->set_param('url-preview', $this->env_config->url_preview ?? null);
			// $this->rencon->req()->set_param('url-production', $this->env_config->url_production ?? null);
			// $this->rencon->req()->set_param('git-url', $this->env_config->git_url ?? null);
			// $this->rencon->req()->set_param('git-username', $this->env_config->git_username ?? null);
			// $this->rencon->req()->set_param('git-password', $this->env_config->git_password ?? null);
		}

		if( $this->rencon->req()->get_param('m') == 'save' ){
			$this->edit_save();
			exit;
		}

		return $this->edit_input();
	}

	/**
	 * 編集画面: 入力画面
	 */
	private function edit_input(){
?>

<form action="?a=<?= htmlspecialchars($this->rencon->req()->get_param('a') ?? '') ?>" method="post">
	<input type="hidden" name="m" value="save" />
	<input type="hidden" name="ADMIN_USER_CSRF_TOKEN" value="<?= htmlspecialchars($this->rencon->auth()->get_csrf_token()) ?>" />

	<!-- ID/PWのオートコンプリートを無効にするためのダミー入力欄 -->
	<input type="password" name="autocomplete-off" value="" style="position: absolute; visibility: hidden; top: -100px; left: -100px;" />


	<div class="px2-form-input-list">
		<ul class="px2-form-input-list__ul">
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-command-php">PHPコマンド</label></div>
				<div class="px2-form-input-list__input">
					<input type="text" id="input-command-php" name="input-command-php" value="<?= htmlspecialchars($this->rencon->req()->get_param('command-php') ?? '') ?>" class="px2-input px2-input--block" />
				</div>
			</li>
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-command-git">Gitコマンド</label></div>
				<div class="px2-form-input-list__input">
					<input type="text" id="input-command-git" name="input-command-git" value="<?= htmlspecialchars($this->rencon->req()->get_param('command-git') ?? '') ?>" class="px2-input px2-input--block" />
				</div>
			</li>
		</ul>
	</div>

	<!-- <div class="px2-form-input-list">
		<ul class="px2-form-input-list__ul">
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-url-preview">プレビューURL</label></div>
				<div class="px2-form-input-list__input">
					<input type="text" id="input-url-preview" name="input-url-preview" value="<?= htmlspecialchars($this->rencon->req()->get_param('url-preview') ?? '') ?>" class="px2-input px2-input--block" />
				</div>
			</li>
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-url-production">本番URL</label></div>
				<div class="px2-form-input-list__input">
					<input type="text" id="input-url-production" name="input-url-production" value="<?= htmlspecialchars($this->rencon->req()->get_param('url-production') ?? '') ?>" class="px2-input px2-input--block" />
				</div>
			</li>
		</ul>
	</div>

	<div class="px2-form-input-list">
		<ul class="px2-form-input-list__ul">
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-git-url">Gitリモート URL</label></div>
				<div class="px2-form-input-list__input"><input type="text" id="input-git-url" name="input-git-url" value="<?= htmlspecialchars($this->rencon->req()->get_param('git-url') ?? '') ?>" class="px2-input px2-input--block" /></div>
			</li>
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-git-username">Gitリモート ユーザー名</label></div>
				<div class="px2-form-input-list__input"><input type="text" id="input-git-username" name="input-git-username" value="<?= htmlspecialchars($this->rencon->req()->get_param('git-username') ?? '') ?>" class="px2-input px2-input--block" /></div>
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
	</div> -->

	<p class="px2-text-align-center"><button class="px2-btn px2-btn--primary">保存する</button></p>
</form>

<?php
		return;
	}


	/**
	 * 編集画面: 保存処理を実行する
	 */
	private function edit_save(){
		$this->env_config->commands->php = $this->rencon->req()->get_param('input-command-php');
		$this->env_config->commands->git = $this->rencon->req()->get_param('input-command-git');
		// $this->env_config->url_preview = $this->rencon->req()->get_param('input-url-preview');
		// $this->env_config->url_production = $this->rencon->req()->get_param('input-url-production');
		// $this->env_config->git_url = $this->rencon->req()->get_param('input-git-url');
		// $this->env_config->git_username = $this->rencon->req()->get_param('input-git-username');
		// if( strlen($this->rencon->req()->get_param('input-git-password') ?? '') ){
		// 	$this->env_config->git_password = $this->rencon->req()->get_param('input-git-password');
		// }
		$this->env_config->save();

		header("Location: ?a=".htmlspecialchars($this->rencon->req()->get_param('a') ?? '').'&m=completed');
		exit;
	}


	/**
	 * 編集画面: 完了画面
	 */
	private function edit_view_completed(){
?>

<p>保存しました。</p>
<p><a href="?a=env_config" class="px2-btn px2-btn--primary">完了</a></p>

<?php
		return;
	}

}
