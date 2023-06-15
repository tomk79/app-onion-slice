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
		return $ctrl->index__route();
	}

	/**
	 * 編集画面
	 */
	static public function edit( $rencon ){
		$ctrl = new self($rencon);
		return $ctrl->edit__route();
	}

	/**
	 * リモート情報編集画面
	 */
	static public function remote_edit( $rencon ){
		$ctrl = new self($rencon);
		return $ctrl->remote_edit__route();
	}


	/**
	 * Constructor
	 */
	public function __construct( $rencon ){
		$this->rencon = $rencon;
		$this->env_config = new \tomk79\onionSlice\model\env_config( $this->rencon );
	}

	// --------------------------------------

	/**
	 * 設定トップ画面: ルーティング
	 */
	private function index__route(){
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

	<h2>リモート設定</h2>
	<div class="px2-p">
		<table class="px2-table">
			<tbody>
<?php
		foreach($this->env_config->remotes as $remote_uri => $remote_info){
			?><tr>
				<td><?= htmlspecialchars($remote_uri) ?></td>
				<td><?= htmlspecialchars($remote_info->type ?? '---') ?></td>
				<td><a href="?a=env_config.remote.edit&remote_uri=<?= htmlspecialchars(urlencode($remote_uri)) ?>" class="px2-btn px2-btn--primary">編集</a></td>
			</tr><?php
		}
?>
			</tbody>
		</table>
	</div>
<?php

		return;
	}

	// --------------------------------------

	/**
	 * 編集画面: ルーティング
	 */
	private function edit__route(){

		if( $this->rencon->req()->get_param('m') == 'completed' ){
			return $this->edit__completed();
		}

		if( !strlen($this->rencon->req()->get_param('m') ?? '') ){
			$this->rencon->req()->set_param('command-php', $this->env_config->commands->php ?? null);
			$this->rencon->req()->set_param('command-git', $this->env_config->commands->git ?? null);
		}

		if( $this->rencon->req()->get_param('m') == 'save' ){
			$this->edit__save();
			exit;
		}

		return $this->edit__input();
	}

	/**
	 * 編集画面: 入力画面
	 */
	private function edit__input(){
?>

<form action="?a=<?= htmlspecialchars($this->rencon->req()->get_param('a') ?? '') ?>" method="post">
	<input type="hidden" name="m" value="save" />
	<input type="hidden" name="ADMIN_USER_CSRF_TOKEN" value="<?= htmlspecialchars($this->rencon->auth()->get_csrf_token()) ?>" />

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

	<p class="px2-text-align-center"><button class="px2-btn px2-btn--primary">保存する</button></p>
</form>

<?php
		return;
	}


	/**
	 * 編集画面: 保存処理を実行する
	 */
	private function edit__save(){
		$this->env_config->commands->php = $this->rencon->req()->get_param('input-command-php');
		$this->env_config->commands->git = $this->rencon->req()->get_param('input-command-git');
		$this->env_config->save();

		header("Location: ?a=".htmlspecialchars($this->rencon->req()->get_param('a') ?? '').'&m=completed');
		exit;
	}


	/**
	 * 編集画面: 完了画面
	 */
	private function edit__completed(){
?>

<p>保存しました。</p>
<p><a href="?a=env_config" class="px2-btn px2-btn--primary">完了</a></p>

<?php
		return;
	}


	// --------------------------------------

	/**
	 * リモート情報編集画面: ルーティング
	 */
	private function remote_edit__route(){

		if( $this->rencon->req()->get_param('m') == 'completed' ){
			return $this->remote_edit__completed();
		}

		if( !strlen($this->rencon->req()->get_param('m') ?? '') ){
			$remote_uri = $this->rencon->req()->get_param('remote_uri');
			$remote_info = $this->env_config->remotes->{$remote_uri};
			$this->rencon->req()->set_param('type', $remote_info->type);
			$this->rencon->req()->set_param('username', $remote_info->username);
		}

		if( $this->rencon->req()->get_param('m') == 'save' ){
			$this->remote_edit__save();
			exit;
		}

		return $this->remote_edit__input();
	}

	/**
	 * リモート情報編集画面: 入力画面
	 */
	private function remote_edit__input(){
?>

<form action="?a=<?= htmlspecialchars($this->rencon->req()->get_param('a') ?? '') ?>" method="post">
	<input type="hidden" name="m" value="save" />
	<input type="hidden" name="remote_uri" value="<?= htmlspecialchars( $this->rencon->req()->get_param('remote_uri') ) ?>" />
	<input type="hidden" name="ADMIN_USER_CSRF_TOKEN" value="<?= htmlspecialchars($this->rencon->auth()->get_csrf_token()) ?>" />

	<!-- ID/PWのオートコンプリートを無効にするためのダミー入力欄 -->
	<input type="password" name="autocomplete-off" value="" style="position: absolute; visibility: hidden; top: -100px; left: -100px;" />


	<div class="px2-form-input-list">
		<ul class="px2-form-input-list__ul">
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-command-php">Remote URI</label></div>
				<div class="px2-form-input-list__input">
					<?= htmlspecialchars( $this->rencon->req()->get_param('remote_uri') ) ?>
				</div>
			</li>
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-type">Type</label></div>
				<div class="px2-form-input-list__input">
					<input type="text" id="input-type" name="input-type" value="<?= htmlspecialchars($this->rencon->req()->get_param('type') ?? '') ?>" class="px2-input px2-input--block" />
				</div>
			</li>
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-username">User Name</label></div>
				<div class="px2-form-input-list__input">
					<input type="text" id="input-username" name="input-username" value="<?= htmlspecialchars($this->rencon->req()->get_param('username') ?? '') ?>" class="px2-input px2-input--block" />
				</div>
			</li>
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-password">パスワード</label></div>
				<div class="px2-form-input-list__input">
					<input type="password" id="input-password" name="input-password" value="" class="px2-input px2-input--block" />
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
	 * リモート情報編集画面: 保存処理を実行する
	 */
	private function remote_edit__save(){
		$remote_uri = $this->rencon->req()->get_param('remote_uri');
		$this->env_config->remotes->{$remote_uri}->type = $this->rencon->req()->get_param('input-type');
		$this->env_config->remotes->{$remote_uri}->username = $this->rencon->req()->get_param('input-username');
		if( strlen($this->rencon->req()->get_param('input-password') ?? '') ){
			$crypt = new \tomk79\onionSlice\helpers\crypt( $this->rencon );
			$this->env_config->remotes->{$remote_uri}->password = $crypt->encrypt($this->rencon->req()->get_param('input-password'));
		}
		$this->env_config->save();

		header("Location: ?a=".htmlspecialchars($this->rencon->req()->get_param('a') ?? '').'&m=completed');
		exit;
	}


	/**
	 * リモート情報編集画面: 完了画面
	 */
	private function remote_edit__completed(){
?>

<p>保存しました。</p>
<p><a href="?a=env_config" class="px2-btn px2-btn--primary">完了</a></p>

<?php
		return;
	}

}
