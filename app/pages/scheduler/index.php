<?php
$projects = new \tomk79\onionSlice\model\projects($rencon);
$project_id = $rencon->get_route_param('projectId');
$project = $projects->get($project_id);
$scheduler = $project->scheduler();
$now = time();
?>

<link rel="stylesheet" href="?res=scheduler/scheduler.css" />
<script src="?res=scheduler/scheduler.js"></script>


<ul class="px2-horizontal-list px2-horizontal-list--right">
	<li><a href="?a=proj.<?= htmlspecialchars($project_id) ?>.scheduler.tasks" class="px2-btn">履歴</a></li>
	<li><a href="?a=proj.<?= htmlspecialchars($project_id) ?>.scheduler.create" class="px2-btn px2-btn--primary">新規配信予定</a></li>
</ul>
<?php
$active_schedules = $scheduler->get_schedule_all();
$current_schedule_id = $scheduler->get_current_schedule_id();
if( !count(array_keys(get_object_vars($active_schedules))) ){
	echo "<p>配信予定はありません。</p>";
}else{
	$active_schedules_keys = array_keys(get_object_vars($active_schedules));
	$active_schedules_keys = array_reverse($active_schedules_keys);
	?>
	<div class="px2-responsive">
		<table class="px2-table" style="width:100%;">
			<thead>
				<tr>
					<th>公開予定日時</th>
					<th>リビジョン</th>
					<th>タイトル</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
			<?php
			foreach( $active_schedules_keys as $schedule_id ){
				$schedule_info = $active_schedules->{$schedule_id};
				$class_name = '';
				if($current_schedule_id === $schedule_id){
					$class_name = ' class="px2-table__cont-current"';
				}elseif(strtotime($scheduler->parse_release_at($schedule_id) ?? '') < $now){
					$class_name = ' class="px2-table__cont-past"';
				}
				?>
				<tr<?= $class_name ?>>
					<td><?= htmlspecialchars($schedule_info->release_at ?? '---') ?></td>
					<td><?= htmlspecialchars($schedule_info->revision ?? '---') ?></td>
					<td><?= htmlspecialchars($schedule_info->title ?? '---') ?></td>
					<td><a href="?a=proj.<?= htmlspecialchars($project_id ?? '') ?>.scheduler.<?= htmlspecialchars($schedule_id ?? '') ?>.detail" class="px2-btn px2-btn--primary">詳細</a></td>
				</tr>
				<?php
			}
			?>
			</tbody>
		</table>
	</div>
	<?php
}
?>
