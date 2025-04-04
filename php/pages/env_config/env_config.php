<?php
namespace tomk79\onionSlice\pages\env_config;

class env_config {

	private $rencon;
	private $env_config;
	private $api_keys;

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
	 * リモート情報新規作成画面
	 */
	static public function remote_create( $rencon ){
		$ctrl = new self($rencon);
		return $ctrl->remote_create__route();
	}

	/**
	 * リモート情報編集画面
	 */
	static public function remote_edit( $rencon ){
		$ctrl = new self($rencon);
		return $ctrl->remote_edit__route();
	}

	/**
	 * リモート情報削除画面
	 */
	static public function remote_delete( $rencon ){
		$ctrl = new self($rencon);
		return $ctrl->remote_delete__route();
	}

	/**
	 * APIキー作成API
	 */
	static public function api_key_create( $rencon ){
		$ctrl = new self($rencon);
		return $ctrl->api_key_create__route();
	}

	/**
	 * APIキー削除画面
	 */
	static public function api_key_delete( $rencon ){
		$ctrl = new self($rencon);
		return $ctrl->api_key_delete__route();
	}


	/**
	 * Constructor
	 */
	public function __construct( $rencon ){
		$this->rencon = $rencon;
		$this->env_config = new \tomk79\onionSlice\model\env_config( $this->rencon );
		$this->api_keys = new \tomk79\onionSlice\model\api_keys( $this->rencon );
	}


	// --------------------------------------

	/**
	 * 設定トップ画面: ルーティング
	 */
	private function index__route(){
		$api_keys = $this->api_keys->get_api_keys();
		return $this->index__startpage($api_keys);
	}

	/**
	 * 設定トップ画面: スタートページ
	 */
	private function index__startpage($api_keys){
?>

	<h2>コマンドのパス設定</h2>
	<table class="px2-table px2-table--dl">
		<tbody>
			<tr>
				<th>PHPコマンド</th>
				<td><?= htmlspecialchars($this->env_config->commands->php ?? '') ?></td>
			</tr>
			<tr>
				<th>Gitコマンド</th>
				<td><?= htmlspecialchars($this->env_config->commands->git ?? '') ?></td>
			</tr>
		</tbody>
	</table>

	<p class="px2-text-align-right">
		<a href="?a=env_config.edit" class="px2-btn px2-btn--primary">編集する</a>
	</p>

	<h2>アカウント</h2>
	<div class="px2-linklist">
		<ul>
			<li><a href="?a=env_config.profile.edit">プロフィール設定</a></li>
		</ul>
	</div>

	<div class="px2-h2">
		<div class="px2-h2__shoulder"><a href="?a=env_config.remote.create" class="px2-btn px2-btn--primary">新規作成</a></div>
		<h2>リモート設定</h2>
	</div>
	<div class="px2-p">
		<div class="px2-responsive">
			<table class="px2-table" style="width: 100%;">
				<thead>
					<tr>
						<th>リモートURI</th>
						<th>タイプ</th>
						<th>ユーザー名</th>
						<th></th>
						<th></th>
					</tr>
				</thead>
				<tbody>
<?php
		foreach($this->env_config->remotes as $remote_uri => $remote_info){
			?><tr>
				<td><?= htmlspecialchars($remote_uri) ?></td>
				<td><?= htmlspecialchars($remote_info->type ?? '---') ?></td>
				<td><?= htmlspecialchars($remote_info->username ?? '---') ?></td>
				<td class="px2-text-align-center"><a href="?a=env_config.remote.edit&remote_uri=<?= htmlspecialchars(urlencode($remote_uri)) ?>" class="px2-btn px2-btn--primary">編集</a></td>
				<td class="px2-text-align-center"><a href="?a=env_config.remote.delete&remote_uri=<?= htmlspecialchars(urlencode($remote_uri)) ?>" class="px2-btn px2-btn--danger">削除</a></td>
			</tr><?php
		}
?>
				</tbody>
			</table>
		</div>
	</div>
	<div class="px2-h2">
		<div class="px2-h2__shoulder"><a href="?a=env_config.api_keys.create" class="px2-btn px2-btn--primary" id="cont-btn-create-api-key">新規作成</a></div>
		<h2>APIキー</h2>
	</div>
	<div class="px2-p">
		<div class="px2-responsive">
			<table class="px2-table" style="width: 100%;">
				<thead>
					<tr>
						<th>APIキー</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
<?php
		foreach($api_keys as $api_key){
			?><tr>
				<td><?= htmlspecialchars(str_pad($api_key ?? '', 32, '*', STR_PAD_RIGHT)) ?></td>
				<td class="px2-text-align-center"><a href="?a=env_config.api_keys.<?= htmlspecialchars(urlencode($api_key)) ?>.delete" class="px2-btn px2-btn--danger">削除</a></td>
			</tr><?php
		}
?>
				</tbody>
			</table>
		</div>
	</div>
	<script>
		$('#cont-btn-create-api-key').on('click', function(event){
			event.preventDefault();

			$.ajax({
				"url": '?a=env_config.api_keys.create',
				"type": "post",
				"data": {
					'CSRF_TOKEN': $('meta[name="csrf-token"]').attr('content'),
				},
			}).done((result)=>{
				px2style.modal({
					title: '新しいAPIキー',
					body: `<div>
						<p>APIキー: <code>${result.key}</code></p>
					</div>`,
					onclose: ()=>{
						window.location.reload();
					}
				});
			})
			.fail((err)=>{
				console.error(err);
				alert('APIキーの作成に失敗しました。');
			});
		});
	</script>
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

		$validationResult = $this->edit__validate();

		if( !strlen($this->rencon->req()->get_param('m') ?? '') ){
			$validationResult->result = true;
			$validationResult->errors = new \stdClass();
			$this->rencon->req()->set_param('input-command-php', $this->env_config->commands->php ?? null);
			$this->rencon->req()->set_param('input-command-git', $this->env_config->commands->git ?? null);
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


	// --------------------------------------

	/**
	 * リモート情報新規作成画面: ルーティング
	 */
	private function remote_create__route(){

		if( $this->rencon->req()->get_param('m') == 'completed' ){
			return $this->remote_create__completed();
		}

		$validationResult = $this->remote_create__validate();

		if( !strlen($this->rencon->req()->get_param('m') ?? '') ){
			$validationResult->result = true;
			$validationResult->errors = new \stdClass();
		}

		if( $this->rencon->req()->get_param('m') == 'save' && $validationResult->result ){
			$this->rencon->utils->api_post_only();
			$this->remote_create__save();
			exit;
		}

		return $this->remote_create__input($validationResult);
	}

	/**
	 * リモート情報新規作成画面: 入力画面
	 */
	private function remote_create__input($validationResult){
?>

<form action="?a=<?= htmlspecialchars($this->rencon->req()->get_param('a') ?? '') ?>" method="post">
	<input type="hidden" name="m" value="save" />
	<input type="hidden" name="CSRF_TOKEN" value="<?= htmlspecialchars($this->rencon->auth()->get_csrf_token()) ?>" />

	<!-- ID/PWのオートコンプリートを無効にするためのダミー入力欄 -->
	<input type="password" name="autocomplete-off" value="" style="position: absolute; visibility: hidden; top: -100px; left: -100px;" />


	<div class="px2-form-input-list">
		<ul class="px2-form-input-list__ul">
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-remote_uri">リモートURI</label></div>
				<div class="px2-form-input-list__input">
					<?php if( strlen($validationResult->errors->{'input-remote_uri'} ?? '') ){ ?><div class="px2-error"><?= htmlspecialchars($validationResult->errors->{'input-remote_uri'}) ?></div><?php } ?>
					<input type="text" id="input-remote_uri" name="input-remote_uri" value="<?= htmlspecialchars($this->rencon->req()->get_param('remote_uri') ?? '') ?>" class="px2-input px2-input--block" />
				</div>
			</li>
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-type">タイプ</label></div>
				<div class="px2-form-input-list__input">
					<?php if( strlen($validationResult->errors->{'input-type'} ?? '') ){ ?><div class="px2-error"><?= htmlspecialchars($validationResult->errors->{'input-type'}) ?></div><?php } ?>
					<select id="input-type" name="input-type" class="px2-input px2-input--block">
						<option value="git" <?= ($this->rencon->req()->get_param('input-type') == 'git' ? 'selected="selected"' : '') ?>>git</option>
					</select>
				</div>
			</li>
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-username">ユーザー名</label></div>
				<div class="px2-form-input-list__input">
					<?php if( strlen($validationResult->errors->{'input-username'} ?? '') ){ ?><div class="px2-error"><?= htmlspecialchars($validationResult->errors->{'input-username'}) ?></div><?php } ?>
					<input type="text" id="input-username" name="input-username" value="<?= htmlspecialchars($this->rencon->req()->get_param('username') ?? '') ?>" class="px2-input px2-input--block" />
				</div>
			</li>
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-password">パスワード</label></div>
				<div class="px2-form-input-list__input">
					<?php if( strlen($validationResult->errors->{'input-password'} ?? '') ){ ?><div class="px2-error"><?= htmlspecialchars($validationResult->errors->{'input-password'}) ?></div><?php } ?>
					<input type="password" id="input-password" name="input-password" value="" class="px2-input px2-input--block" />
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
	 * リモート情報新規作成画面: バリデーション
	 */
	private function remote_create__validate(){
		$validationResult = (object) array(
			'result' => true,
			'errors' => (object) array(),
		);

		if( !strlen($this->rencon->req()->get_param('input-remote_uri') ?? '') ){
			$validationResult->result = false;
			$validationResult->errors->{'input-remote_uri'} = 'リモートURIは必須項目です。';
		}elseif( ($this->env_config->remotes->{$this->rencon->req()->get_param('input-remote_uri')} ?? null) ){
			$validationResult->result = false;
			$validationResult->errors->{'remote_uri'} = 'このリモートURIはすでに定義されています。';
		}

		if( !strlen($this->rencon->req()->get_param('input-type') ?? '') ){
			$validationResult->result = false;
			$validationResult->errors->{'input-type'} = 'タイプは必須項目です。';
		}

		return $validationResult;
	}

	/**
	 * リモート情報新規作成画面: 保存処理を実行する
	 */
	private function remote_create__save(){
		$remote_uri = $this->rencon->req()->get_param('input-remote_uri');
		$this->env_config->remotes->{$remote_uri} = new \stdClass();
		$this->env_config->remotes->{$remote_uri}->type = $this->rencon->req()->get_param('input-type');
		$this->env_config->remotes->{$remote_uri}->username = $this->rencon->req()->get_param('input-username');
		$this->env_config->remotes->{$remote_uri}->password = null;
		if( strlen($this->rencon->req()->get_param('input-password') ?? '') ){
			$crypt = new \tomk79\onionSlice\helpers\crypt( $this->rencon );
			$this->env_config->remotes->{$remote_uri}->password = $crypt->encrypt($this->rencon->req()->get_param('input-password'));
		}
		$this->env_config->save();

		header("Location: ?a=".htmlspecialchars($this->rencon->req()->get_param('a') ?? '').'&m=completed');
		exit;
	}


	/**
	 * リモート情報新規作成画面: 完了画面
	 */
	private function remote_create__completed(){
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

		$validationResult = $this->remote_edit__validate();

		if( !strlen($this->rencon->req()->get_param('m') ?? '') ){
			$validationResult->result = true;
			$validationResult->errors = new \stdClass();
			$remote_uri = $this->rencon->req()->get_param('remote_uri');
			$remote_info = $this->env_config->remotes->{$remote_uri};
			$this->rencon->req()->set_param('input-type', $remote_info->type);
			$this->rencon->req()->set_param('input-username', $remote_info->username);
		}

		if( $this->rencon->req()->get_param('m') == 'save' && $validationResult->result ){
			$this->rencon->utils->api_post_only();
			$this->remote_edit__save();
			exit;
		}

		return $this->remote_edit__input($validationResult);
	}

	/**
	 * リモート情報編集画面: 入力画面
	 */
	private function remote_edit__input($validationResult){
?>

<form action="?a=<?= htmlspecialchars($this->rencon->req()->get_param('a') ?? '') ?>" method="post">
	<input type="hidden" name="m" value="save" />
	<input type="hidden" name="remote_uri" value="<?= htmlspecialchars( $this->rencon->req()->get_param('remote_uri') ) ?>" />
	<input type="hidden" name="CSRF_TOKEN" value="<?= htmlspecialchars($this->rencon->auth()->get_csrf_token()) ?>" />

	<!-- ID/PWのオートコンプリートを無効にするためのダミー入力欄 -->
	<input type="password" name="autocomplete-off" value="" style="position: absolute; visibility: hidden; top: -100px; left: -100px;" />


	<div class="px2-form-input-list">
		<ul class="px2-form-input-list__ul">
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-command-php">リモートURI</label></div>
				<div class="px2-form-input-list__input">
					<?php if( strlen($validationResult->errors->{'remote_uri'} ?? '') ){ ?><div class="px2-error"><?= htmlspecialchars($validationResult->errors->{'remote_uri'}) ?></div><?php } ?>
					<?= htmlspecialchars( $this->rencon->req()->get_param('remote_uri') ) ?>
				</div>
			</li>
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-type">タイプ</label></div>
				<div class="px2-form-input-list__input">
					<?php if( strlen($validationResult->errors->{'input-type'} ?? '') ){ ?><div class="px2-error"><?= htmlspecialchars($validationResult->errors->{'input-type'}) ?></div><?php } ?>
					<select id="input-type" name="input-type" class="px2-input px2-input--block">
						<option value="git" <?= ($this->rencon->req()->get_param('input-type') == 'git' ? 'selected="selected"' : '') ?>>git</option>
					</select>
				</div>
			</li>
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-username">ユーザー名</label></div>
				<div class="px2-form-input-list__input">
					<?php if( strlen($validationResult->errors->{'input-username'} ?? '') ){ ?><div class="px2-error"><?= htmlspecialchars($validationResult->errors->{'input-username'}) ?></div><?php } ?>
					<input type="text" id="input-username" name="input-username" value="<?= htmlspecialchars($this->rencon->req()->get_param('input-username') ?? '') ?>" class="px2-input px2-input--block" />
				</div>
			</li>
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-password">パスワード</label></div>
				<div class="px2-form-input-list__input">
					<?php if( strlen($validationResult->errors->{'input-password'} ?? '') ){ ?><div class="px2-error"><?= htmlspecialchars($validationResult->errors->{'input-password'}) ?></div><?php } ?>
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
	 * リモート情報新規作成画面: バリデーション
	 */
	private function remote_edit__validate(){
		$validationResult = (object) array(
			'result' => true,
			'errors' => (object) array(),
		);

		if( !strlen($this->rencon->req()->get_param('remote_uri') ?? '') ){
			$validationResult->result = false;
			$validationResult->errors->{'remote_uri'} = 'リモートURIは必須項目です。';
		}elseif( !($this->env_config->remotes->{$this->rencon->req()->get_param('remote_uri')} ?? null) ){
			$validationResult->result = false;
			$validationResult->errors->{'remote_uri'} = 'このリモートURIは未定義です。';
		}

		if( !strlen($this->rencon->req()->get_param('input-type') ?? '') ){
			$validationResult->result = false;
			$validationResult->errors->{'input-type'} = 'タイプは必須項目です。';
		}

		return $validationResult;
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

	// --------------------------------------

	/**
	 * リモート情報削除画面: ルーティング
	 */
	private function remote_delete__route(){

		if( $this->rencon->req()->get_param('m') == 'completed' ){
			return $this->remote_delete__completed();
		}

		$validationResult = $this->remote_delete__validate();

		if( !strlen($this->rencon->req()->get_param('m') ?? '') ){
			$validationResult->result = true;
			$validationResult->errors = new \stdClass();
		}

		if( $this->rencon->req()->get_param('m') == 'save' && $validationResult->result ){
			$this->rencon->utils->api_post_only();
			$this->remote_delete__save();
			exit;
		}

		return $this->remote_delete__input($validationResult);
	}

	/**
	 * リモート情報削除画面: 入力画面
	 */
	private function remote_delete__input($validationResult){
?>

<form action="?a=<?= htmlspecialchars($this->rencon->req()->get_param('a') ?? '') ?>" method="post">
	<input type="hidden" name="m" value="save" />
	<input type="hidden" name="remote_uri" value="<?= htmlspecialchars( $this->rencon->req()->get_param('remote_uri') ) ?>" />
	<input type="hidden" name="CSRF_TOKEN" value="<?= htmlspecialchars($this->rencon->auth()->get_csrf_token()) ?>" />

	<p>リモート情報 <code><?= htmlspecialchars( $this->rencon->req()->get_param('remote_uri') ) ?></code> を削除します。</p>
	<p>この操作は取り消せません。</p>
	<p>本当に削除しますか？</p>

	<p class="px2-text-align-center"><button class="px2-btn px2-btn--danger">削除する</button></p>
</form>

<?php
		return;
	}

	/**
	 * リモート情報新規作成画面: バリデーション
	 */
	private function remote_delete__validate(){
		$validationResult = (object) array(
			'result' => true,
			'errors' => (object) array(),
		);

		if( !strlen($this->rencon->req()->get_param('remote_uri') ?? '') ){
			$validationResult->result = false;
			$validationResult->errors->{'remote_uri'} = 'リモートURIは必須項目です。';
		}elseif( !($this->env_config->remotes->{$this->rencon->req()->get_param('remote_uri')} ?? null) ){
			$validationResult->result = false;
			$validationResult->errors->{'remote_uri'} = 'このリモートURIは未定義です。';
		}

		return $validationResult;
	}

	/**
	 * リモート情報削除画面: 保存処理を実行する
	 */
	private function remote_delete__save(){
		$remote_uri = $this->rencon->req()->get_param('remote_uri');
		unset($this->env_config->remotes->{$remote_uri});
		$this->env_config->save();

		header("Location: ?a=".htmlspecialchars($this->rencon->req()->get_param('a') ?? '').'&m=completed');
		exit;
	}

	/**
	 * リモート情報削除画面: 完了画面
	 */
	private function remote_delete__completed(){
?>

<p>削除しました。</p>
<p><a href="?a=env_config" class="px2-btn px2-btn--primary">完了</a></p>

<?php
		return;
	}


	// --------------------------------------

	/**
	 * APIキー作成API: ルーティング
	 */
	public function api_key_create__route(){
		return $this->api_key_create__json_api();
	}
	public function api_key_create__json_api(){
		$api_key = $this->api_keys->create_new_api_key();

		header('Content-type: text/json');
		echo json_encode(array(
			'result' => !!$api_key,
			'message' => ($api_key ? 'OK' : 'Failed'),
			'key' => $api_key,
		));
		exit;
	}

	// --------------------------------------

	/**
	 * APIキー削除画面: ルーティング
	 */
	public function api_key_delete__route(){

		if( $this->rencon->req()->get_param('m') == 'completed' ){
			return $this->api_key_delete__completed();
		}

		$validationResult = $this->api_key_delete__validate();

		if( !strlen($this->rencon->req()->get_param('m') ?? '') ){
			$validationResult->result = true;
			$validationResult->errors = new \stdClass();
		}

		if( $this->rencon->req()->get_param('m') == 'save' && $validationResult->result ){
			$this->rencon->utils->api_post_only();
			$this->api_key_delete__save();
			exit;
		}

		return $this->api_key_delete__input($validationResult);
		return $this->api_key_delete__input();
	}

	/**
	 * APIキー削除画面: ルーティング
	 */
	public function api_key_delete__input($validationResult){
?>

<form action="?a=<?= htmlspecialchars($this->rencon->req()->get_param('a') ?? '') ?>" method="post">
	<input type="hidden" name="m" value="save" />
	<input type="hidden" name="CSRF_TOKEN" value="<?= htmlspecialchars($this->rencon->auth()->get_csrf_token()) ?>" />

	<p>APIキー <code><?= htmlspecialchars( str_pad($this->rencon->get_route_param('apiKeyId') ?? '', 32, '*', STR_PAD_RIGHT) ) ?></code> を削除します。</p>
	<p>この操作は取り消せません。</p>
	<p>本当に削除しますか？</p>

	<p class="px2-text-align-center"><button class="px2-btn px2-btn--danger">削除する</button></p>
</form>

<?php
		return;
	}

	/**
	 * APIキー削除画面: バリデーション
	 */
	private function api_key_delete__validate(){
		$validationResult = (object) array(
			'result' => true,
			'errors' => (object) array(),
		);

		return $validationResult;
	}

	/**
	 * APIキー削除画面: 保存処理を実行する
	 */
	private function api_key_delete__save(){
		$api_key_initial10 = $this->rencon->get_route_param('apiKeyId');
		$result = $this->api_keys->delete_api_key($api_key_initial10);

		header("Location: ?a=".htmlspecialchars($this->rencon->req()->get_param('a') ?? '').'&m=completed&result='.urlencode($result ? '1' : '0'));
		exit;
	}

	/**
	 * APIキー削除画面: 完了画面
	 */
	private function api_key_delete__completed(){
?>

<p>削除しました。</p>
<p><a href="?a=env_config" class="px2-btn px2-btn--primary">完了</a></p>

<?php
		return;
	}
}
