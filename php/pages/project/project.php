<?php
namespace tomk79\onionSlice\pages\project;

class project {

	private $rencon;
	private $projects;
	private $project_id;
	private $env_config;

	/**
	 * 新規作成画面
	 */
	static public function create( $rencon ){
		$ctrl = new self($rencon);
		return $ctrl->create__route();
	}

	/**
	 * 編集画面
	 */
	static public function edit( $rencon ){
		$ctrl = new self($rencon);
		return $ctrl->edit__route();
	}

	/**
	 * 削除画面
	 */
	static public function delete( $rencon ){
		$ctrl = new self($rencon);
		return $ctrl->delete__route();
	}


	/**
	 * Constructor
	 */
	public function __construct( $rencon ){
		$this->rencon = $rencon;
		$this->projects = new \tomk79\onionSlice\model\projects( $this->rencon );
		$this->project_id = $rencon->get_route_param('projectId');
		$this->env_config = new \tomk79\onionSlice\model\env_config( $this->rencon );
	}


	// --------------------------------------

	/**
	 * 新規作成画面: ルーティング
	 */
	private function create__route(){

		if( $this->rencon->req()->get_param('m') == 'completed' ){
			return $this->create__completed();
		}

		$validationResult = $this->create__validate();

		if( !strlen($this->rencon->req()->get_param('m') ?? '') ){
			$validationResult->result = true;
			$validationResult->errors = new \stdClass();
			$this->rencon->req()->set_param('input-type', 'directory');
			$this->rencon->req()->set_param('input-realpath_base_dir', dirname(__DIR__) ?? null);
		}

		if( $this->rencon->req()->get_param('m') == 'save' && $validationResult->result ){
			$this->rencon->utils->api_post_only();
			return $this->create__save();
		}

		return $this->create__input($validationResult);
	}

	/**
	 * 新規作成画面: 入力画面
	 */
	private function create__input($validationResult){
?>

<link rel="stylesheet" href="?res=directory_suggestion/directory_suggestion.css" />
<script src="?res=directory_suggestion/directory_suggestion.js"></script>

<link rel="stylesheet" href="?res=project_form/project_form.css" />
<script src="?res=project_form/project_form.js"></script>

<form action="?a=<?= htmlspecialchars($this->rencon->req()->get_param('a') ?? '') ?>" method="post">
	<input type="hidden" name="m" value="save" />
	<input type="hidden" name="CSRF_TOKEN" value="<?= htmlspecialchars($this->rencon->auth()->get_csrf_token()) ?>" />

	<div class="px2-form-input-list">
		<ul class="px2-form-input-list__ul">
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-id">プロジェクトID</label></div>
				<div class="px2-form-input-list__input">
					<?php if( strlen($validationResult->errors->{'input-id'} ?? '') ){ ?><div class="px2-error"><?= htmlspecialchars($validationResult->errors->{'input-id'}) ?></div><?php } ?>
					<input type="text" id="input-id" name="input-id" value="<?= htmlspecialchars($this->rencon->req()->get_param('input-id') ?? '') ?>" class="px2-input px2-input--block" />
				</div>
			</li>
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-name">プロジェクト名</label></div>
				<div class="px2-form-input-list__input">
					<?php if( strlen($validationResult->errors->{'input-name'} ?? '') ){ ?><div class="px2-error"><?= htmlspecialchars($validationResult->errors->{'input-name'}) ?></div><?php } ?>
					<input type="text" id="input-name" name="input-name" value="<?= htmlspecialchars($this->rencon->req()->get_param('input-name') ?? '') ?>" class="px2-input px2-input--block" />
				</div>
			</li>
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-url">URL</label></div>
				<div class="px2-form-input-list__input">
					<?php if( strlen($validationResult->errors->{'input-url'} ?? '') ){ ?><div class="px2-error"><?= htmlspecialchars($validationResult->errors->{'input-url'}) ?></div><?php } ?>
					<input type="text" id="input-url" name="input-url" value="<?= htmlspecialchars($this->rencon->req()->get_param('input-url') ?? '') ?>" class="px2-input px2-input--block" />
				</div>
			</li>
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-url_admin">管理画面のURL</label></div>
				<div class="px2-form-input-list__input">
					<?php if( strlen($validationResult->errors->{'input-url_admin'} ?? '') ){ ?><div class="px2-error"><?= htmlspecialchars($validationResult->errors->{'input-url_admin'}) ?></div><?php } ?>
					<input type="text" id="input-url_admin" name="input-url_admin" value="<?= htmlspecialchars($this->rencon->req()->get_param('input-url_admin') ?? '') ?>" class="px2-input px2-input--block" />
				</div>
			</li>
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-type">プロジェクトタイプ</label></div>
				<div class="px2-form-input-list__input">
					<?php if( strlen($validationResult->errors->{'input-type'} ?? '') ){ ?><div class="px2-error"><?= htmlspecialchars($validationResult->errors->{'input-type'}) ?></div><?php } ?>
					<label><input type="radio" id="input-type--directory" name="input-type" value="directory" <?= $this->rencon->req()->get_param('input-type') == 'directory' ? 'checked="checked"' : '' ?> class="px2-input px2-input--block" /> ディレクトリ</label>
					<label><input type="radio" id="input-type--scheduler" name="input-type" value="scheduler" <?= $this->rencon->req()->get_param('input-type') == 'scheduler' ? 'checked="checked"' : '' ?> class="px2-input px2-input--block" /> スケジューラー</label>
				</div>
			</li>
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-realpath_base_dir">ベースディレクトリ</label></div>
				<div class="px2-form-input-list__input">
					<?php if( strlen($validationResult->errors->{'input-realpath_base_dir'} ?? '') ){ ?><div class="px2-error"><?= htmlspecialchars($validationResult->errors->{'input-realpath_base_dir'}) ?></div><?php } ?>
					<input type="text" id="input-realpath_base_dir" name="input-realpath_base_dir" value="<?= htmlspecialchars($this->rencon->req()->get_param('input-realpath_base_dir') ?? '') ?>" class="px2-input px2-input--block" />
				</div>
			</li>
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-remote">リモートURI</label></div>
				<div class="px2-form-input-list__input">
					<?php if( strlen($validationResult->errors->{'input-remote'} ?? '') ){ ?><div class="px2-error"><?= htmlspecialchars($validationResult->errors->{'input-remote'}) ?></div><?php } ?>
					<select id="input-remote" name="input-remote" class="px2-input px2-input--block">
						<option value="">---</option>
						<?php foreach($this->env_config->remotes as $uri_remote => $remote_info){ ?>
						<option value="<?= htmlspecialchars($uri_remote) ?>" <?= ($this->rencon->req()->get_param('input-remote') == $uri_remote ? 'selected="selected"' : '') ?>><?= htmlspecialchars($uri_remote) ?></option>
						<?php } ?>
					</select>
				</div>
			</li>
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-staging">ステージング</label></div>
				<div class="px2-form-input-list__input">
					<?php if( strlen($validationResult->errors->{'input-staging'} ?? '') ){ ?><div class="px2-error"><?= htmlspecialchars($validationResult->errors->{'input-staging'}) ?></div><?php } ?>
					<select id="input-staging" name="input-staging" class="px2-input px2-input--block">
						<option value="">---</option>
						<?php foreach($this->projects->get_projects() as $project_id => $project_info){ ?>
						<option value="<?= htmlspecialchars($project_id) ?>" <?= ($this->rencon->req()->get_param('input-staging') == $project_id ? 'selected="selected"' : '') ?> <?= (($project_info->type??'directory')!='directory'||$project_id == $this->project_id ? 'disabled="disabled"' : '') ?>><?= htmlspecialchars($project_info->name) ?></option>
						<?php } ?>
					</select>
				</div>
			</li>
		</ul>
	</div>

	<p class="px2-text-align-center"><button class="px2-btn px2-btn--primary">保存する</button></p>
</form>

<script>
window.bindDirectorySuggestion('#input-realpath_base_dir');
</script>

<?php
		return;
	}

	/**
	 * 新規作成画面: バリデーション
	 */
	private function create__validate(){
		$validationResult = (object) array(
			'result' => true,
			'errors' => (object) array(),
		);

		if( !strlen($this->rencon->req()->get_param('input-id') ?? '') ){
			$validationResult->result = false;
			$validationResult->errors->{'input-id'} = 'IDは必須項目です。';
		}elseif($this->projects->get_project( $this->rencon->req()->get_param('input-id') )){
			$validationResult->result = false;
			$validationResult->errors->{'input-id'} = 'そのIDはすでに存在します。';
		}elseif(!preg_match( '/^[a-zA-Z0-9]([a-zA-Z0-9\-\_]*[a-zA-Z0-9])?$/', $this->rencon->req()->get_param('input-id') )){
			$validationResult->result = false;
			$validationResult->errors->{'input-id'} = 'a-z, A-Z, 0-9 以外の文字を含めることはできません。 区切り文字として -, _ が使えます。';
		}

		if( !strlen($this->rencon->req()->get_param('input-name') ?? '') ){
			$validationResult->result = false;
			$validationResult->errors->{'input-name'} = 'プロジェクト名は必須項目です。';
		}

		if( !strlen($this->rencon->req()->get_param('input-name') ?? '') ){
			$validationResult->result = false;
			$validationResult->errors->{'input-type'} = 'プロジェクトタイプは必須項目です。';
		}

		return $validationResult;
	}

	/**
	 * 新規作成画面: 保存処理を実行する
	 */
	private function create__save(){

		$project = (object) array();
		$project->name = $this->rencon->req()->get_param('input-name');
		$project->url = $this->rencon->req()->get_param('input-url');
		$project->url_admin = $this->rencon->req()->get_param('input-url_admin');
		$project->type = $this->rencon->req()->get_param('input-type');
		$project->realpath_base_dir = $this->rencon->req()->get_param('input-realpath_base_dir');
		$project->remote = $this->rencon->req()->get_param('input-remote');
		$project->staging = $this->rencon->req()->get_param('input-staging');
		$this->projects->set_project($this->rencon->req()->get_param('input-id'), $project);
		$result = $this->projects->save();
		if( !$result ){
			$validationResult = (object) array(
				'result' => true,
				'errors' => (object) array(),
			);
			$validationResult->result = false;
			$validationResult->errors->{'input-id'} = '保存に失敗しました。';
			return $this->create__input($validationResult);
		}

		header("Location: ?a=".htmlspecialchars($this->rencon->req()->get_param('a') ?? '').'&m=completed');
		exit;
	}


	/**
	 * 新規作成画面: 完了画面
	 */
	private function create__completed(){
?>

<p>保存しました。</p>
<p><a href="?a=" class="px2-btn px2-btn--primary">完了</a></p>

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
			$project = $this->projects->get_project($this->project_id);
			$this->rencon->req()->set_param('input-name', $project->name ?? null);
			$this->rencon->req()->set_param('input-url', $project->url ?? null);
			$this->rencon->req()->set_param('input-url_admin', $project->url_admin ?? null);
			$this->rencon->req()->set_param('input-type', $project->type ?? 'directory');
			$this->rencon->req()->set_param('input-realpath_base_dir', $project->realpath_base_dir ?? null);
			$this->rencon->req()->set_param('input-remote', $project->remote ?? null);
		}

		if( $this->rencon->req()->get_param('m') == 'save' && $validationResult->result ){
			$this->rencon->utils->api_post_only();
			return $this->edit__save();
		}

		return $this->edit__input($validationResult);
	}

	/**
	 * 編集画面: 入力画面
	 */
	private function edit__input($validationResult){
?>

<link rel="stylesheet" href="?res=directory_suggestion/directory_suggestion.css" />
<script src="?res=directory_suggestion/directory_suggestion.js"></script>

<link rel="stylesheet" href="?res=project_form/project_form.css" />
<script src="?res=project_form/project_form.js"></script>

<form action="?a=<?= htmlspecialchars($this->rencon->req()->get_param('a') ?? '') ?>" method="post">
	<input type="hidden" name="m" value="save" />
	<input type="hidden" name="CSRF_TOKEN" value="<?= htmlspecialchars($this->rencon->auth()->get_csrf_token()) ?>" />

	<div class="px2-form-input-list">
		<ul class="px2-form-input-list__ul">
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-name">プロジェクト名</label></div>
				<div class="px2-form-input-list__input">
					<?php if( strlen($validationResult->errors->{'input-name'} ?? '') ){ ?><div class="px2-error"><?= htmlspecialchars($validationResult->errors->{'input-name'}) ?></div><?php } ?>
					<input type="text" id="input-name" name="input-name" value="<?= htmlspecialchars($this->rencon->req()->get_param('input-name') ?? '') ?>" class="px2-input px2-input--block" />
				</div>
			</li>
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-url">URL</label></div>
				<div class="px2-form-input-list__input">
					<?php if( strlen($validationResult->errors->{'input-url'} ?? '') ){ ?><div class="px2-error"><?= htmlspecialchars($validationResult->errors->{'input-url'}) ?></div><?php } ?>
					<input type="text" id="input-url" name="input-url" value="<?= htmlspecialchars($this->rencon->req()->get_param('input-url') ?? '') ?>" class="px2-input px2-input--block" />
				</div>
			</li>
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-url_admin">管理画面のURL</label></div>
				<div class="px2-form-input-list__input">
					<?php if( strlen($validationResult->errors->{'input-url_admin'} ?? '') ){ ?><div class="px2-error"><?= htmlspecialchars($validationResult->errors->{'input-url_admin'}) ?></div><?php } ?>
					<input type="text" id="input-url_admin" name="input-url_admin" value="<?= htmlspecialchars($this->rencon->req()->get_param('input-url_admin') ?? '') ?>" class="px2-input px2-input--block" />
				</div>
			</li>
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-type">プロジェクトタイプ</label></div>
				<div class="px2-form-input-list__input">
					<?php if( strlen($validationResult->errors->{'input-type'} ?? '') ){ ?><div class="px2-error"><?= htmlspecialchars($validationResult->errors->{'input-type'}) ?></div><?php } ?>
					<label><input type="radio" id="input-type--directory" name="input-type" value="directory" <?= $this->rencon->req()->get_param('input-type') == 'directory' ? 'checked="checked"' : '' ?> class="px2-input px2-input--block" /> ディレクトリ</label>
					<label><input type="radio" id="input-type--scheduler" name="input-type" value="scheduler" <?= $this->rencon->req()->get_param('input-type') == 'scheduler' ? 'checked="checked"' : '' ?> class="px2-input px2-input--block" /> スケジューラー</label>
				</div>
			</li>
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-realpath_base_dir">ベースディレクトリ</label></div>
				<div class="px2-form-input-list__input">
					<?php if( strlen($validationResult->errors->{'input-realpath_base_dir'} ?? '') ){ ?><div class="px2-error"><?= htmlspecialchars($validationResult->errors->{'input-realpath_base_dir'}) ?></div><?php } ?>
					<input type="text" id="input-realpath_base_dir" name="input-realpath_base_dir" value="<?= htmlspecialchars($this->rencon->req()->get_param('input-realpath_base_dir') ?? '') ?>" class="px2-input px2-input--block" />
				</div>
			</li>
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-remote">リモートURI</label></div>
				<div class="px2-form-input-list__input">
					<?php if( strlen($validationResult->errors->{'input-remote'} ?? '') ){ ?><div class="px2-error"><?= htmlspecialchars($validationResult->errors->{'input-remote'}) ?></div><?php } ?>
					<select id="input-remote" name="input-remote" class="px2-input px2-input--block">
						<option value="">---</option>
						<?php foreach($this->env_config->remotes as $uri_remote => $remote_info){ ?>
						<option value="<?= htmlspecialchars($uri_remote) ?>" <?= ($this->rencon->req()->get_param('input-remote') == $uri_remote ? 'selected="selected"' : '') ?>><?= htmlspecialchars($uri_remote) ?></option>
						<?php } ?>
					</select>
				</div>
			</li>
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-staging">ステージング</label></div>
				<div class="px2-form-input-list__input">
					<?php if( strlen($validationResult->errors->{'input-staging'} ?? '') ){ ?><div class="px2-error"><?= htmlspecialchars($validationResult->errors->{'input-staging'}) ?></div><?php } ?>
					<select id="input-staging" name="input-staging" class="px2-input px2-input--block">
						<option value="">---</option>
						<?php foreach($this->projects->get_projects() as $project_id => $project_info){ ?>
						<option value="<?= htmlspecialchars($project_id) ?>" <?= ($this->rencon->req()->get_param('input-staging') == $project_id ? 'selected="selected"' : '') ?> <?= (($project_info->type??'directory')!='directory'||$project_id == $this->project_id ? 'disabled="disabled"' : '') ?>><?= htmlspecialchars($project_info->name) ?></option>
						<?php } ?>
					</select>
				</div>
			</li>
		</ul>
	</div>

	<p class="px2-text-align-center"><button class="px2-btn px2-btn--primary">保存する</button></p>
</form>
<script>
window.bindDirectorySuggestion('#input-realpath_base_dir');
</script>

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

		if( !strlen($this->project_id ?? '') ){
			$validationResult->result = false;
			$validationResult->errors->{'input-id'} = 'IDは必須項目です。';
		}elseif(!$this->projects->get_project( $this->project_id )){
			$validationResult->result = false;
			$validationResult->errors->{'input-id'} = 'そのIDは存在しません。';
		}

		if( !strlen($this->rencon->req()->get_param('input-name') ?? '') ){
			$validationResult->result = false;
			$validationResult->errors->{'input-name'} = 'プロジェクト名は必須項目です。';
		}

		if( !strlen($this->rencon->req()->get_param('input-name') ?? '') ){
			$validationResult->result = false;
			$validationResult->errors->{'input-type'} = 'プロジェクトタイプは必須項目です。';
		}

		return $validationResult;
	}


	/**
	 * 編集画面: 保存処理を実行する
	 */
	private function edit__save(){
		$project = $this->projects->get_project($this->project_id);
		if( !$project ){
			$project = (object) array();
		}

		$project->name = $this->rencon->req()->get_param('input-name');
		$project->url = $this->rencon->req()->get_param('input-url');
		$project->url_admin = $this->rencon->req()->get_param('input-url_admin');
		$project->type = $this->rencon->req()->get_param('input-type');
		$project->realpath_base_dir = $this->rencon->req()->get_param('input-realpath_base_dir');
		$project->remote = $this->rencon->req()->get_param('input-remote');
		$project->staging = $this->rencon->req()->get_param('input-staging');
		$result = $this->projects->save();
		if( !$result ){
			$validationResult = (object) array(
				'result' => true,
				'errors' => (object) array(),
			);
			$validationResult->result = false;
			$validationResult->errors->{'input-id'} = '保存に失敗しました。';
			return $this->edit__input($validationResult);
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
<p><a href="?a=proj.<?= htmlspecialchars(urlencode($this->project_id)) ?>" class="px2-btn px2-btn--primary">完了</a></p>

<?php
		return;
	}

	// --------------------------------------

	/**
	 * 削除画面: ルーティング
	 */
	private function delete__route(){

		if( $this->rencon->req()->get_param('m') == 'completed' ){
			return $this->delete__completed();
		}

		$validationResult = $this->delete__validate();

		if( !strlen($this->rencon->req()->get_param('m') ?? '') ){
			$validationResult->result = true;
			$validationResult->errors = new \stdClass();
		}

		if( $this->rencon->req()->get_param('m') == 'save' && $validationResult->result ){
			$this->rencon->utils->api_post_only();
			return $this->delete__save();
		}

		return $this->delete__input($validationResult);
	}

	/**
	 * 削除画面: 入力画面
	 */
	private function delete__input($validationResult){
?>

<form action="?a=<?= htmlspecialchars($this->rencon->req()->get_param('a') ?? '') ?>" method="post">
	<input type="hidden" name="m" value="save" />
	<input type="hidden" name="CSRF_TOKEN" value="<?= htmlspecialchars($this->rencon->auth()->get_csrf_token()) ?>" />

	<p>この操作は取り消せません。</p>
	<p>本当に削除しますか？</p>

	<p class="px2-text-align-center"><button class="px2-btn px2-btn--danger">削除する</button></p>
</form>

<?php
		return;
	}

	/**
	 * 削除画面: バリデーション
	 */
	private function delete__validate(){
		$validationResult = (object) array(
			'result' => true,
			'errors' => (object) array(),
		);

		if( !strlen($this->project_id ?? '') ){
			$validationResult->result = false;
			$validationResult->errors->{'input-id'} = 'IDは必須項目です。';
		}elseif(!$this->projects->get_project( $this->project_id )){
			$validationResult->result = false;
			$validationResult->errors->{'input-id'} = 'そのIDは存在しません。';
		}

		return $validationResult;
	}


	/**
	 * 削除画面: 保存処理を実行する
	 */
	private function delete__save(){
		$this->projects->delete_project($this->project_id);
		$result = $this->projects->save();
		if( !$result ){
			$validationResult = (object) array(
				'result' => true,
				'errors' => (object) array(),
			);
			$validationResult->result = false;
			$validationResult->errors->{'input-id'} = '保存に失敗しました。';
			return $this->delete__input($validationResult);
		}

		header("Location: ?a=".htmlspecialchars($this->rencon->req()->get_param('a') ?? '').'&m=completed');
		exit;
	}


	/**
	 * 削除画面: 完了画面
	 */
	private function delete__completed(){
?>

<p>削除しました。</p>
<p><a href="?a=" class="px2-btn px2-btn--primary">完了</a></p>

<?php
		return;
	}

}
