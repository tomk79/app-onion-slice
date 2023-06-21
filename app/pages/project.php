<?php
$projects = new \tomk79\onionSlice\model\projects($rencon);
$project_id = $rencon->get_route_param('projectId');
$project_info = $projects->get_project($project_id);
?>

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
				<td><?= htmlspecialchars( $project_info->realpath_base_dir ?? '---' ) ?></td>
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
