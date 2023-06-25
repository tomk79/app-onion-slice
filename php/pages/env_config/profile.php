<?php
namespace tomk79\onionSlice\pages\env_config;

class profile {

	private $rencon;
	private $env_config;

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
			$validationResult->result = true;
			$validationResult->errors = new \stdClass();
			$this->rencon->req()->set_param('input-command-php', $this->env_config->commands->php ?? null);
			$this->rencon->req()->set_param('input-command-git', $this->env_config->commands->git ?? null);
		}

		if( $this->rencon->req()->get_param('m') == 'save' && $validationResult->result ){
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
	<input type="hidden" name="ADMIN_USER_CSRF_TOKEN" value="<?= htmlspecialchars($this->rencon->auth()->get_csrf_token()) ?>" />

	<div class="px2-form-input-list">
		<ul class="px2-form-input-list__ul">
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-command-php">PHPコマンド</label></div>
				<div class="px2-form-input-list__input">
					<?php if( strlen($validationResult->errors->{'input-command-php'} ?? '') ){ ?><div class="px2-error"><?= htmlspecialchars($validationResult->errors->{'input-command-php'}) ?></div><?php } ?>
					<input type="text" id="input-command-php" name="input-command-php" value="<?= htmlspecialchars($this->rencon->req()->get_param('input-command-php') ?? '') ?>" class="px2-input px2-input--block" />
				</div>
			</li>
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-command-git">Gitコマンド</label></div>
				<div class="px2-form-input-list__input">
					<?php if( strlen($validationResult->errors->{'input-command-git'} ?? '') ){ ?><div class="px2-error"><?= htmlspecialchars($validationResult->errors->{'input-command-git'}) ?></div><?php } ?>
					<input type="text" id="input-command-git" name="input-command-git" value="<?= htmlspecialchars($this->rencon->req()->get_param('input-command-git') ?? '') ?>" class="px2-input px2-input--block" />
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

}
