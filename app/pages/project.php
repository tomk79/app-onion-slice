<?php
$projects = new \tomk79\onionSlice\model\projects($rencon);
$project_id = $rencon->get_route_param('projectId');
$project_info = $projects->get_project($project_id);
$project = $projects->get($project_id);
?>

<script>
/**
 * 空のディレクトリを作成する
 */
window.contCreateEmptyBaseDir = function(){
	if( !confirm( 'ディレクトリを作成します。'+"\n"+<?= var_export($project_info->realpath_base_dir, true) ?>+"\n"+'よろしいですか？' ) ){
		return;
	}
	var projectId = <?= var_export($project_id ?? null, true); ?>;
	window.px2style.loading();
	$.ajax({
		"url": `?a=api.${projectId}.initialize_project.mk_empty_base_dir`,
		"type": "post",
		"data": {
			'CSRF_TOKEN': $('meta[name="csrf-token"]').attr('content'),
		},
	}).done(function(res) {
		if( !res.result ){
			console.error('Error:', res);
			alert('ディレクトリの作成に失敗しました。');
			return;
		}
		alert('ディレクトリを作成しました。');
	}).fail(function() {
		alert('Errored');
	}).always(function() {
		window.px2style.closeLoading();
		window.location.reload();
	});
}
/**
 * git clone で初期化する
 */
window.contInitializeWithGitRemote = function(){
	if( !confirm( 'git remote で初期化します。'+"\n"+'よろしいですか？' ) ){
		return;
	}
	var projectId = <?= var_export($project_id ?? null, true); ?>;
	window.px2style.loading();
	$.ajax({
		"url": `?a=api.${projectId}.initialize_project.initialize_with_git_remote`,
		"type": "post",
		"data": {
			'CSRF_TOKEN': $('meta[name="csrf-token"]').attr('content'),
		},
	}).done(function(res) {
		if( !res.result ){
			console.error('Error:', res);
			alert('初期化に失敗しました。');
			return;
		}
		alert('初期化しました。');
	}).fail(function() {
		alert('Errored');
	}).always(function() {
		window.px2style.closeLoading();
		window.location.reload();
	});
}
/**
 * composer create-project pickles2/pickles2 で初期化する
 */
window.contInitializeWithPickles2 = function(){
	if( !confirm( 'Pickles 2 で初期化します。'+"\n"+'よろしいですか？' ) ){
		return;
	}
	var projectId = <?= var_export($project_id ?? null, true); ?>;
	window.px2style.loading();
	$.ajax({
		"url": `?a=api.${projectId}.initialize_project.initialize_with_pickles2`,
		"type": "post",
		"data": {
			'CSRF_TOKEN': $('meta[name="csrf-token"]').attr('content'),
		},
	}).done(function(res) {
		if( !res.result ){
			console.error('Error:', res);
			alert('初期化に失敗しました。');
			return;
		}
		alert('初期化しました。');
	}).fail(function() {
		alert('Errored');
	}).always(function() {
		window.px2style.closeLoading();
		window.location.reload();
	});
}
/**
 * git init する
 */
window.contGitInit = function(){
	if( !confirm( 'git init します。'+"\n"+'よろしいですか？' ) ){
		return;
	}
	var projectId = <?= var_export($project_id ?? null, true); ?>;
	window.px2style.loading();
	$.ajax({
		"url": `?a=api.${projectId}.initialize_project.git_init`,
		"type": "post",
		"data": {
			'CSRF_TOKEN': $('meta[name="csrf-token"]').attr('content'),
		},
	}).done(function(res) {
		if( !res.result ){
			console.error('Error:', res);
			alert('初期化に失敗しました。');
			return;
		}
		alert('初期化しました。');
	}).fail(function() {
		alert('Errored');
	}).always(function() {
		window.px2style.closeLoading();
		window.location.reload();
	});
}
</script>

<?php if( $project->is_project_base_dir_empty() ){ ?>
<div class="px2-notice">
	<p>ベースディレクトリは空白です。</p>
	<div class="px2-linklist">
		<ul class="px2-linklist__horizontal px2-linklist__horizontal--center">
			<?php if( strlen($project_info->remote ?? '') ){ ?>
			<li><button type="button" class="px2-btn px2-btn--primary" onclick="contInitializeWithGitRemote()">git clone で初期化</button></li>
			<?php } ?>
			<li><button type="button" class="px2-btn px2-btn--primary" onclick="contInitializeWithPickles2()">Pickles 2 で初期化</button></li>
		</ul>
	</div>
</div>
<?php } ?>

<div class="px2-p">
	<table class="px2-table px2-table--dl">
		<tbody>
			<tr>
				<th>プロジェクト名</th>
				<td><b><?= htmlspecialchars( $project_info->name ?? '---' ) ?></b></td>
			</tr>
			<tr>
				<th>URL</th>
				<td><a href="<?= htmlspecialchars($project_info->url ?? 'about:blank') ?>" target="_blank"><?= htmlspecialchars( $project_info->url ?? '---' ) ?></a></td>
			</tr>
			<tr>
				<th>管理画面のURL</th>
				<td><a href="<?= htmlspecialchars($project_info->url_admin ?? 'about:blank') ?>" target="_blank"><?= htmlspecialchars( $project_info->url_admin ?? '---' ) ?></a></td>
			</tr>
			<tr>
				<th>ベースディレクトリ</th>
				<td>
					<?= htmlspecialchars( $project_info->realpath_base_dir ?? '---' ) ?>
					<?php if( !strlen($project_info->realpath_base_dir ?? '') ){ ?>
						<p>ベースディレクトリが設定されていません。</p>
					<?php }elseif( !is_dir($project_info->realpath_base_dir) ){ ?>
						<p>ベースディレクトリが存在しません。 <button type="button" class="px2-btn px2-btn--primary" onclick="contCreateEmptyBaseDir()">作成する</button></p>
					<?php } ?>
				</td>
			</tr>
			<tr>
				<th>リモートURI</th>
				<td><?= htmlspecialchars( $project_info->remote ?? '---' ) ?></td>
			</tr>
		</tbody>
	</table>
	<p class="px2-text-align-right">
		<a href="?a=proj.<?= htmlspecialchars(urlencode($project_id)) ?>.edit" class="px2-btn px2-btn--primary">編集</a>
	</p>
</div>

<?php if( !$project->is_project_base_dir_empty() && $project->base_dir_exists() && !$project->has_dot_git() ){ ?>
<div class="px2-notice">
	<p>gitローカルリポジトリが初期化されていません。</p>
	<div class="px2-linklist">
		<ul class="px2-linklist__horizontal px2-linklist__horizontal--center">
			<li><button type="button" class="px2-btn px2-btn--primary" onclick="contGitInit()">git init する</button></li>
		</ul>
	</div>
</div>
<?php } ?>

<div class="px2-p">
	<p class="px2-text-align-center">
		<a href="?a=proj.<?= htmlspecialchars(urlencode($project_id)) ?>.delete" class="px2-btn px2-btn--danger">削除</a>
	</p>
</div>
