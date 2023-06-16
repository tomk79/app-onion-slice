<?php
$projects = new \tomk79\onionSlice\model\projects($rencon);
$project_id = $rencon->get_route_param('projectId');
$project_info = $projects->get_project($project_id);
?>

<div class="px2-p">
	<table class="px2-table px2-table--dl">
		<tbody>
			<tr>
				<th>Name</th>
				<td><b><?= htmlspecialchars( $project_info->name ?? '---' ) ?></b></td>
			</tr>
			<tr>
				<th>URL</th>
				<td><a href="<?= htmlspecialchars($project_info->url ?? '') ?>" target="_blank"><?= htmlspecialchars( $project_info->url ?? '---' ) ?></a></td>
			</tr>
			<tr>
				<th>realpath_base_dir</th>
				<td><?= htmlspecialchars( $project_info->realpath_base_dir ?? '---' ) ?></td>
			</tr>
			<tr>
				<th>remote</th>
				<td><?= htmlspecialchars( $project_info->remote ?? '---' ) ?></td>
			</tr>
		</tbody>
	</table>
	<p class="px2-text-align-right">
		<a href="?a=proj.<?= htmlspecialchars(urlencode($project_id)) ?>.edit" class="px2-btn px2-btn--primary">編集</a>
	</p>
</div>
