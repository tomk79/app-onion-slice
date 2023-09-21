<?php
namespace tomk79\onionSlice\pages\env_config;

class profile {

	private $rencon;
	private $env_config;
	private $profile;

	/**
	 * 編集画面
	 */
	static public function edit( $rencon ){
		$ctrl = new self($rencon);
		return $ctrl->edit__route();
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
	 * 編集画面: ルーティング
	 */
	private function edit__route(){

		if( $this->rencon->req()->get_param('m') == 'completed' ){
			return $this->edit__completed();
		}

		$validationResult = $this->edit__validate();

		if( !strlen($this->rencon->req()->get_param('m') ?? '') ){
			$validationResult->is_valid = true;
			$validationResult->errors = new \stdClass();
			$profile = $this->rencon->auth()->get_login_user_info();
			$this->rencon->req()->set_param('input-name', $profile->name ?? null);
			$this->rencon->req()->set_param('input-id', $profile->id ?? null);
			$this->rencon->req()->set_param('input-pw', null);
			$this->rencon->req()->set_param('input-lang', $profile->lang ?? null);
			$this->rencon->req()->set_param('input-email', $profile->email ?? null);
			$this->rencon->req()->set_param('input-role', $profile->role ?? null);
		}

		if( $this->rencon->req()->get_param('m') == 'save' && $validationResult->is_valid ){
			$this->rencon->utils->api_post_only();
			$this->edit__save();
			exit;
		}

		return $this->edit__input($validationResult);
	}

	/**
	 * 編集画面: 入力画面
	 */
	private function edit__input($validationResult){
?>

<form action="?a=<?= htmlspecialchars($this->rencon->req()->get_param('a') ?? '') ?>" method="post">
	<input type="hidden" name="m" value="save" />
	<input type="hidden" name="CSRF_TOKEN" value="<?= htmlspecialchars($this->rencon->auth()->get_csrf_token()) ?>" />

	<div class="px2-form-input-list">
		<ul class="px2-form-input-list__ul">
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-current_pw">現在のパスワード</label></div>
				<div class="px2-form-input-list__input">
					<?php if( count($validationResult->errors->{'current_pw'} ?? array()) ){ ?><div class="px2-error"><?= htmlspecialchars(implode('<br />', $validationResult->errors->{'current_pw'})) ?></div><?php } ?>
					<input type="password" id="input-current_pw" name="input-current_pw" value="<?= htmlspecialchars($this->rencon->req()->get_param('input-current_pw') ?? '') ?>" class="px2-input px2-input--block" />
				</div>
			</li>
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-name">ユーザー名</label></div>
				<div class="px2-form-input-list__input">
					<?php if( count($validationResult->errors->{'name'} ?? array()) ){ ?><div class="px2-error"><?= htmlspecialchars(implode('<br />', $validationResult->errors->{'name'})) ?></div><?php } ?>
					<input type="text" id="input-name" name="input-name" value="<?= htmlspecialchars($this->rencon->req()->get_param('input-name') ?? '') ?>" class="px2-input px2-input--block" />
				</div>
			</li>
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-id">ユーザーID</label></div>
				<div class="px2-form-input-list__input">
					<?php if( count($validationResult->errors->{'id'} ?? array()) ){ ?><div class="px2-error"><?= htmlspecialchars(implode('<br />', $validationResult->errors->{'id'})) ?></div><?php } ?>
					<input type="text" id="input-id" name="input-id" value="<?= htmlspecialchars($this->rencon->req()->get_param('input-id') ?? '') ?>" class="px2-input px2-input--block" />
				</div>
			</li>
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-pw">パスワード</label></div>
				<div class="px2-form-input-list__input">
					<?php if( count($validationResult->errors->{'pw'} ?? array()) ){ ?><div class="px2-error"><?= htmlspecialchars(implode('<br />', $validationResult->errors->{'pw'})) ?></div><?php } ?>
					<input type="password" id="input-pw" name="input-pw" value="<?= htmlspecialchars($this->rencon->req()->get_param('input-pw') ?? '') ?>" class="px2-input px2-input--block" />
					<?php if( count($validationResult->errors->{'pw_retype'} ?? array()) ){ ?><div class="px2-error"><?= htmlspecialchars(implode('<br />', $validationResult->errors->{'pw_retype'})) ?></div><?php } ?>
					<input type="password" id="input-pw" name="input-pw_retype" value="<?= htmlspecialchars($this->rencon->req()->get_param('input-pw_retype') ?? '') ?>" class="px2-input px2-input--block" />
				</div>
			</li>
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-lang">言語</label></div>
				<div class="px2-form-input-list__input">
					<?php if( count($validationResult->errors->{'lang'} ?? array()) ){ ?><div class="px2-error"><?= htmlspecialchars(implode('<br />', $validationResult->errors->{'lang'})) ?></div><?php } ?>
					<select id="input-lang" name="input-lang" class="px2-input">
						<option value="ja" <?= $this->rencon->req()->get_param('input-lang') == 'ja' ? 'selected="selected"' : '' ?>>Japanese</option>
						<option value="en" <?= $this->rencon->req()->get_param('input-lang') == 'en' ? 'selected="selected"' : '' ?>>English</option>
					</select>
				</div>
			</li>
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-email">メールアドレス</label></div>
				<div class="px2-form-input-list__input">
					<?php if( count($validationResult->errors->{'email'} ?? array()) ){ ?><div class="px2-error"><?= htmlspecialchars(implode('<br />', $validationResult->errors->{'email'})) ?></div><?php } ?>
					<input type="text" id="input-email" name="input-email" value="<?= htmlspecialchars($this->rencon->req()->get_param('input-email') ?? '') ?>" class="px2-input px2-input--block" />
				</div>
			</li>
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-role">役割</label></div>
				<div class="px2-form-input-list__input">
					<?php if( count($validationResult->errors->{'role'} ?? array()) ){ ?><div class="px2-error"><?= htmlspecialchars(implode('<br />', $validationResult->errors->{'role'})) ?></div><?php } ?>
					<input type="text" id="input-role" name="input-role" value="<?= htmlspecialchars($this->rencon->req()->get_param('input-role') ?? '') ?>" class="px2-input px2-input--block" />
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
	 * 編集画面: バリデーション
	 */
	private function edit__validate(){
		$login_password = $this->rencon->req()->get_param('input-current_pw');

		$new_profile = (object) array();
		$new_profile->name = $this->rencon->req()->get_param('input-name');
		$new_profile->id = $this->rencon->req()->get_param('input-id');
		$new_profile->pw = $this->rencon->req()->get_param('input-pw');
		$new_profile->pw_retype = $this->rencon->req()->get_param('input-pw_retype');
		$new_profile->lang = $this->rencon->req()->get_param('input-lang');
		$new_profile->email = $this->rencon->req()->get_param('input-email');
		$new_profile->role = $this->rencon->req()->get_param('input-role');

		$rtn = $this->rencon->auth()->validate_user_info($new_profile, $login_password);

		if( !$this->rencon->auth()->verify_user_password($login_password) ){
			$rtn->is_valid = false;
			$rtn->errors->current_pw = array('パスワードが一致しません。');
		}

		if( $new_profile->pw || $new_profile->pw_retype ){
			if( $new_profile->pw !== $new_profile->pw_retype ){
				$rtn->is_valid = false;
				$rtn->errors->pw_retype = array('パスワードが一致しません。');
			}
		}else{
			unset($rtn->errors->pw);
			unset($rtn->errors->pw_retype);
		}

		if( !count(get_object_vars($rtn->errors)) ){
			$rtn->is_valid = true;
		}
		return $rtn;
	}

	/**
	 * 編集画面: 保存処理を実行する
	 */
	private function edit__save(){
		$login_password = $this->rencon->req()->get_param('input-current_pw');

		$new_profile = (object) array();
		$new_profile->name = $this->rencon->req()->get_param('input-name');
		$new_profile->id = $this->rencon->req()->get_param('input-id');
		$new_profile->pw = $this->rencon->req()->get_param('input-pw');
		$new_profile->pw_retype = $this->rencon->req()->get_param('input-pw_retype');
		$new_profile->lang = $this->rencon->req()->get_param('input-lang');
		$new_profile->email = $this->rencon->req()->get_param('input-email');
		$new_profile->role = $this->rencon->req()->get_param('input-role');
		$result = $this->rencon->auth()->update_login_user_info($new_profile, $login_password);

		if( !$result->result ){
			echo "<p>Error</p>";
			exit;
		}

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

}
