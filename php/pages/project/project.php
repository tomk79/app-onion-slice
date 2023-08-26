<?php
namespace tomk79\onionSlice\pages\project;

class project {

	private $rencon;
	private $projects;
	private $project_id;

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
		}

		if( $this->rencon->req()->get_param('m') == 'save' && $validationResult->result ){
			$this->create__save();
			exit;
		}

		return $this->create__input($validationResult);
	}

	/**
	 * 新規作成画面: 入力画面
	 */
	private function create__input($validationResult){
?>

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
					<input type="text" id="input-remote" name="input-remote" value="<?= htmlspecialchars($this->rencon->req()->get_param('input-remote') ?? '') ?>" class="px2-input px2-input--block" />
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
		$project->realpath_base_dir = $this->rencon->req()->get_param('input-realpath_base_dir');
		$project->remote = $this->rencon->req()->get_param('input-remote');
		$this->projects->set_project($this->rencon->req()->get_param('input-id'), $project);
		$this->projects->save();

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
			$this->rencon->req()->set_param('input-realpath_base_dir', $project->realpath_base_dir ?? null);
			$this->rencon->req()->set_param('input-remote', $project->remote ?? null);
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
					<input type="text" id="input-remote" name="input-remote" value="<?= htmlspecialchars($this->rencon->req()->get_param('input-remote') ?? '') ?>" class="px2-input px2-input--block" />
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
		$project->realpath_base_dir = $this->rencon->req()->get_param('input-realpath_base_dir');
		$project->remote = $this->rencon->req()->get_param('input-remote');
		$this->projects->save();

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
			$project = $this->projects->get_project($this->project_id);
			$this->rencon->req()->set_param('input-name', $project->name ?? null);
			$this->rencon->req()->set_param('input-url', $project->url ?? null);
			$this->rencon->req()->set_param('input-url_admin', $project->url_admin ?? null);
			$this->rencon->req()->set_param('input-realpath_base_dir', $project->realpath_base_dir ?? null);
			$this->rencon->req()->set_param('input-remote', $project->remote ?? null);
		}

		if( $this->rencon->req()->get_param('m') == 'save' && $validationResult->result ){
			$this->delete__save();
			exit;
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
		$this->projects->save();

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
