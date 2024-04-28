<?php
$projects = new \tomk79\onionSlice\model\projects($rencon);
$project_id = $rencon->get_route_param('projectId');
$project = $projects->get($project_id);
$scheduler = $project->scheduler();
$task_id = $rencon->get_route_param('taskId');
$task_info = $scheduler->get_task($task_id);
?>


<ul class="px2-horizontal-list">
	<li><a href="?a=proj.<?= htmlspecialchars($project_id) ?>.scheduler.tasks" class="px2-btn">戻る</a></li>
</ul>

<?php
if( !$task_info ){
	?>
	<p>存在しないタスクです。</p>
	<?php
	return;
}
?>

<table class="px2-table px2-table--dl">
	<tbody>
		<tr>
			<th>タスクID</th>
			<td><?= htmlspecialchars($task_id ?? '---') ?></td>
		</tr>
		<tr>
			<th>タスク発行日時</th>
			<td><?= htmlspecialchars($task_info->task_created_at ?? '---') ?></td>
		</tr>
		<tr>
			<th>タスクの種類</th>
			<td><?= htmlspecialchars($task_info->type ?? '---') ?></td>
		</tr>
		<tr>
			<th>スケジュール</th>
			<td><?= htmlspecialchars($task_info->properties->id ?? '---') ?></td>
		</tr>
		<tr>
			<th>リビジョン</th>
			<td><?= htmlspecialchars($task_info->properties->revision ?? '---') ?></td>
		</tr>
	</tbody>
</table>
