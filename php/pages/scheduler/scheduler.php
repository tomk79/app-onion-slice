<?php
namespace tomk79\onionSlice\pages\scheduler;

class scheduler {

	private $rencon;
	private $projects;
	private $project_id;
	private $scheduler;
	private $schedule_id;

	/**
	 * 新規作成画面
	 */
	static public function create( $rencon ){
		$ctrl = new self($rencon);
		return $ctrl->create__route();
	}

	/**
	 * 配信予約の詳細画面
	 */
	static public function detail( $rencon ){
		$ctrl = new self($rencon);
		return $ctrl->detail__route();
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
		$this->scheduler = new \tomk79\onionSlice\model\scheduler( $this->rencon, $this->project_id );
		$this->schedule_id = $rencon->get_route_param('scheduleId');
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

			$date = new \DateTimeImmutable(date('Y-m-d 10:00:00', time()+(24*60*60)));
			$this->rencon->req()->set_param('input-release_at', $date->getTimestamp());

			$project_info = $this->projects->get_project($this->project_id);
			$staging_project_info = $this->projects->get_project( $project_info->staging );
			$gitHelper = new \tomk79\onionSlice\helpers\git($this->rencon, $staging_project_info);
			$current_revision = $gitHelper->get_current_revision();
			$this->rencon->req()->set_param('input-revision', $current_revision);
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

<link rel="stylesheet" href="?res=schedule_form/schedule_form.css" />
<script src="?res=schedule_form/schedule_form.js"></script>

<form action="?a=<?= htmlspecialchars($this->rencon->req()->get_param('a') ?? '') ?>" method="post">
	<input type="hidden" name="m" value="save" />
	<input type="hidden" name="CSRF_TOKEN" value="<?= htmlspecialchars($this->rencon->auth()->get_csrf_token()) ?>" />

	<div class="px2-form-input-list">
		<ul class="px2-form-input-list__ul">
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-revision">リビジョン</label></div>
				<div class="px2-form-input-list__input">
					<?php if( strlen($validationResult->errors->{'input-revision'} ?? '') ){ ?><div class="px2-error"><?= htmlspecialchars($validationResult->errors->{'input-revision'}) ?></div><?php } ?>
					<input type="hidden" id="input-revision" name="input-revision" value="<?= htmlspecialchars($this->rencon->req()->get_param('input-revision') ?? '') ?>" />
					<?= htmlspecialchars($this->rencon->req()->get_param('input-revision') ?? '') ?>
				</div>
			</li>
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-release_at">リリース予定日時</label></div>
				<div class="px2-form-input-list__input">
					<?php if( strlen($validationResult->errors->{'input-release_at'} ?? '') ){ ?><div class="px2-error"><?= htmlspecialchars($validationResult->errors->{'input-release_at'}) ?></div><?php } ?>
					<input type="hidden" id="input-release_at" name="input-release_at" value="<?= htmlspecialchars($this->rencon->req()->get_param('input-release_at') ?? '') ?>" />
					UTC
					<input type="date" name="input-release_at-date" value="" class="px2-input" />
					<select name="input-release_at-hour" class="px2-input">
						<?php for($i = 0; $i < 24; $i ++){ ?>
						<option value="<?= intval($i) ?>"><?= intval($i) ?></option>
						<?php } ?>
					</select>
					:
					<select name="input-release_at-min" class="px2-input">
						<?php for($i = 0; $i < 60; $i ++){ ?>
						<option value="<?= intval($i) ?>"><?= intval($i) ?></option>
						<?php } ?>
					</select>
				</div>
			</li>
		</ul>
	</div>

	<p class="px2-text-align-center"><button class="px2-btn px2-btn--primary">配信予約する</button></p>
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

		$release_at = intval($this->rencon->req()->get_param('input-release_at'));
		if( !strlen($this->rencon->req()->get_param('input-release_at') ?? '') ){
			$validationResult->result = false;
			$validationResult->errors->{'input-release_at'} = 'リリース予定日時は必須項目です。';
		}elseif($release_at <= time()){
			$validationResult->result = false;
			$validationResult->errors->{'input-release_at'} = 'リリース予定日時には、未来の時刻を指定してください。';
		}

		return $validationResult;
	}

	/**
	 * 新規作成画面: 保存処理を実行する
	 */
	private function create__save(){

		$result = $this->scheduler->create_schedule( intval($this->rencon->req()->get_param('input-release_at')), $this->rencon->req()->get_param('input-revision') );
		if( !$result ){
			$validationResult = (object) array(
				'result' => true,
				'errors' => (object) array(),
			);
			$validationResult->result = false;
			$validationResult->errors->{'input-release_at'} = '保存に失敗しました。';
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

<p>配信予約を保存しました。</p>
<p><a href="?a=proj.<?= htmlspecialchars($this->project_id ?? '') ?>.scheduler" class="px2-btn px2-btn--primary">完了</a></p>

<?php
		return;
	}



	// --------------------------------------

	/**
	 * 詳細画面: ルーティング
	 */
	private function detail__route(){
		return $this->detail__index();
	}

	/**
	 * 詳細画面: 詳細画面
	 */
	private function detail__index(){
?>

<p><a href="?a=proj.<?= htmlspecialchars($this->project_id ?? '') ?>.scheduler" class="px2-btn">戻る</a></p>
<p><a href="?a=proj.<?= htmlspecialchars($this->project_id ?? '') ?>.scheduler.<?= htmlspecialchars($this->schedule_id ?? '') ?>.delete" class="px2-btn px2-btn--danger">この配信予約をキャンセルする</a></p>

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
	<p>本当にキャンセルしますか？</p>

	<p class="px2-text-align-center"><button class="px2-btn px2-btn--danger">配信予約をキャンセルする</button></p>
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
		$result = $this->scheduler->delete_schedule( $this->schedule_id );

		header("Location: ?a=".htmlspecialchars($this->rencon->req()->get_param('a') ?? '').'&m=completed');
		exit;
	}


	/**
	 * 削除画面: 完了画面
	 */
	private function delete__completed(){
?>

<p>削除しました。</p>
<p><a href="?a=proj.<?= htmlspecialchars($this->project_id ?? '') ?>.scheduler" class="px2-btn px2-btn--primary">完了</a></p>

<?php
		return;
	}

}
