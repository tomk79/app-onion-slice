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
				<td><a href="<?= htmlspecialchars($project_info->url ?? '') ?>" target="_blank"><?= htmlspecialchars( $project_info->name ?? '---' ) ?></a></td>
			</tr>
			<tr>
				<th>URL</th>
				<td><?= htmlspecialchars( $project_info->url ?? '---' ) ?></td>
			</tr>
			<tr>
				<th>Git URL</th>
				<td><?= htmlspecialchars( $project_info->git_url ?? '---' ) ?></td>
			</tr>
			<tr>
				<th>Git User Name</th>
				<td><?= htmlspecialchars( $project_info->git_username ?? '---' ) ?></td>
			</tr>
		</tbody>
	</table>
</div>
