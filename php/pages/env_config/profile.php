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
		$this->profile = new \tomk79\onionSlice\model\profile( $this->rencon );
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
			$validationResult->result = true;
			$validationResult->errors = new \stdClass();
			$profile = $this->profile->get();
			$this->rencon->req()->set_param('input-name', $profile->name ?? null);
			$this->rencon->req()->set_param('input-id', $profile->id ?? null);
			$this->rencon->req()->set_param('input-pw', null);
			$this->rencon->req()->set_param('input-lang', $profile->lang ?? null);
			$this->rencon->req()->set_param('input-email', $profile->email ?? null);
			$this->rencon->req()->set_param('input-role', $profile->role ?? null);
		}

		if( $this->rencon->req()->get_param('m') == 'save' && $validationResult->result ){
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
				<div class="px2-form-input-list__label"><label for="input-name">ユーザー名</label></div>
				<div class="px2-form-input-list__input">
					<?php if( strlen($validationResult->errors->{'input-name'} ?? '') ){ ?><div class="px2-error"><?= htmlspecialchars($validationResult->errors->{'input-name'}) ?></div><?php } ?>
					<input type="text" id="input-name" name="input-name" value="<?= htmlspecialchars($this->rencon->req()->get_param('input-name') ?? '') ?>" class="px2-input px2-input--block" />
				</div>
			</li>
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-id">ユーザーID</label></div>
				<div class="px2-form-input-list__input">
					<?php if( strlen($validationResult->errors->{'input-id'} ?? '') ){ ?><div class="px2-error"><?= htmlspecialchars($validationResult->errors->{'input-id'}) ?></div><?php } ?>
					<input type="text" id="input-id" name="input-id" value="<?= htmlspecialchars($this->rencon->req()->get_param('input-id') ?? '') ?>" class="px2-input px2-input--block" />
				</div>
			</li>
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-pw">パスワード</label></div>
				<div class="px2-form-input-list__input">
					<?php if( strlen($validationResult->errors->{'input-pw'} ?? '') ){ ?><div class="px2-error"><?= htmlspecialchars($validationResult->errors->{'input-pw'}) ?></div><?php } ?>
					<input type="password" id="input-pw" name="input-pw" value="<?= htmlspecialchars($this->rencon->req()->get_param('input-pw') ?? '') ?>" class="px2-input px2-input--block" />
				</div>
			</li>
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-lang">言語</label></div>
				<div class="px2-form-input-list__input">
					<?php if( strlen($validationResult->errors->{'input-lang'} ?? '') ){ ?><div class="px2-error"><?= htmlspecialchars($validationResult->errors->{'input-lang'}) ?></div><?php } ?>
					<input type="text" id="input-lang" name="input-lang" value="<?= htmlspecialchars($this->rencon->req()->get_param('input-lang') ?? '') ?>" class="px2-input px2-input--block" />
				</div>
			</li>
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-email">メールアドレス</label></div>
				<div class="px2-form-input-list__input">
					<?php if( strlen($validationResult->errors->{'input-email'} ?? '') ){ ?><div class="px2-error"><?= htmlspecialchars($validationResult->errors->{'input-email'}) ?></div><?php } ?>
					<input type="text" id="input-email" name="input-email" value="<?= htmlspecialchars($this->rencon->req()->get_param('input-email') ?? '') ?>" class="px2-input px2-input--block" />
				</div>
			</li>
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-role">役割</label></div>
				<div class="px2-form-input-list__input">
					<?php if( strlen($validationResult->errors->{'input-role'} ?? '') ){ ?><div class="px2-error"><?= htmlspecialchars($validationResult->errors->{'input-role'}) ?></div><?php } ?>
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
		$validationResult = (object) array(
			'result' => true,
			'errors' => (object) array(),
		);

		return $validationResult;
	}

	/**
	 * 編集画面: 保存処理を実行する
	 */
	private function edit__save(){
		$profile = (object) array();
		$profile->name = $this->rencon->req()->get_param('input-name');
		$profile->id = $this->rencon->req()->get_param('input-id');
		$profile->pw = $this->rencon->req()->get_param('input-pw');
		$profile->lang = $this->rencon->req()->get_param('input-lang');
		$profile->email = $this->rencon->req()->get_param('input-email');
		$profile->role = $this->rencon->req()->get_param('input-role');
		$this->profile->update( $profile );

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
