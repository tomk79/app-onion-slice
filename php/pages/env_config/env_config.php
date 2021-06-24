<?php
namespace tomk79\onionSlice\pages\env_config;

class env_config {

	private $rencon;
	private $data;

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
		$this->data = new \tomk79\onionSlice\model\env_config( $this->rencon );
	}

	private function route(){

		if( $this->rencon->req()->get_param('m') == 'completed' ){
			return $this->completed();
		}

		if( !strlen($this->rencon->req()->get_param('m')) ){
			$this->rencon->req()->set_param('git-remote', $this->data->git_remote);
			$this->rencon->req()->set_param('git-user-name', $this->data->git_user_name);
			$this->rencon->req()->set_param('git-password', $this->data->git_password);
		}

		if( $this->rencon->req()->get_param('m') == 'save' ){
			$this->save();
			exit;
		}else{
			return $this->edit();
		}

		return;
	}


	/**
	 * 編集画面
	 */
	private function edit(){

?>

<form action="?a=<?= htmlspecialchars($this->rencon->req()->get_param('a')) ?>" method="post">
	<input type="hidden" name="m" value="save" />

	<!-- ID/PWのオートコンプリートを無効にするためのダミー入力欄 -->
	<input type="password" name="autocomplete-off" value="" style="position: absolute; visibility: hidden; top: -100px; left: -100px;" />


	<div class="px2-form-input-list">
		<ul class="px2-form-input-list__ul">
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-git-remote">Gitリモート URL</label></div>
				<div class="px2-form-input-list__input"><input type="text" id="input-git-remote" name="input-git-remote" value="<?= htmlspecialchars($this->rencon->req()->get_param('git-remote')) ?>" class="px2-input px2-input--block" /></div>
			</li>
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-git-user-name">Gitリモート ユーザー名</label></div>
				<div class="px2-form-input-list__input"><input type="text" id="input-git-user-name" name="input-git-user-name" value="<?= htmlspecialchars($this->rencon->req()->get_param('git-user-name')) ?>" class="px2-input px2-input--block" /></div>
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
		$this->data->git_remote = $this->rencon->req()->get_param('input-git-remote');
		$this->data->git_user_name = $this->rencon->req()->get_param('input-git-user-name');
		if( strlen($this->rencon->req()->get_param('input-git-password')) ){
			$this->data->git_password = $this->rencon->req()->get_param('input-git-password');
		}
		$this->data->save();

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
