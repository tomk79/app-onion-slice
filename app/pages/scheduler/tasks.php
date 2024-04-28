<?php
$projects = new \tomk79\onionSlice\model\projects($rencon);
$project_id = $rencon->get_route_param('projectId');
$project = $projects->get($project_id);
$scheduler = $project->scheduler();
?>


<ul class="px2-horizontal-list">
	<li><a href="?a=proj.<?= htmlspecialchars($project_id) ?>.scheduler" class="px2-btn">戻る</a></li>
</ul>
<?php
$active_tasks = $scheduler->get_task_all();
if( !count(array_keys(get_object_vars($active_tasks))) ){
	echo "<p>配信タスクはありません。</p>";
}else{
	?>
<div class="px2-responsive">
	<table class="px2-table" style="width:100%;">
		<thead>
			<tr>
				<th>タスクID</th>
				<th>操作</th>
				<th>リビジョン</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
		<?php
			$active_tasks_keys = array_keys(get_object_vars($active_tasks));
			$active_tasks_keys = array_reverse($active_tasks_keys);
		?>
		<?php foreach( $active_tasks_keys as $task_id ){
			$task_info = $active_tasks->{$task_id};
			?>
			<tr>
				<td><?= htmlspecialchars($task_id) ?></td>
				<td><?= htmlspecialchars($task_info->type ?? '---') ?></td>
				<td><?= htmlspecialchars($task_info->properties->revision ?? '---') ?></td>
				<td><a href="?a=proj.<?= htmlspecialchars($project_id ?? '') ?>.scheduler.tasks.<?= htmlspecialchars($task_id ?? '') ?>.detail" class="px2-btn px2-btn--primary">詳細</a></td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
</div>
	<?php
}
?>
