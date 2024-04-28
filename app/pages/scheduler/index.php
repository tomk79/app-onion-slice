<?php
$projects = new \tomk79\onionSlice\model\projects($rencon);
$project_id = $rencon->get_route_param('projectId');
$project = $projects->get($project_id);
$scheduler = $project->scheduler();
?>


<ul class="px2-horizontal-list px2-horizontal-list--right">
    <li><a href="?a=proj.<?= htmlspecialchars($project_id) ?>.scheduler.tasks" class="px2-btn">履歴</a></li>
    <li><a href="?a=proj.<?= htmlspecialchars($project_id) ?>.scheduler.create" class="px2-btn px2-btn--primary">新規配信予定</a></li>
</ul>
<?php
$active_schedules = $scheduler->get_schedule_all();
if( !count(array_keys(get_object_vars($active_schedules))) ){
    echo "<p>配信予定はありません。</p>";
}else{
    echo '<div class="px2-responsive">'."\n";
    echo '<table class="px2-table" style="width:100%;">'."\n";
    ?>
    <thead>
    <tr>
        <th>ID</th>
        <th>公開予定日時</th>
        <th>リビジョン</th>
        <th></th>
    </tr>
    </thead>
    <?php
    echo '<tbody>'."\n";
    foreach( $active_schedules as $schedule_id => $schedule_info ){
        ?>
        <tr>
            <td><?= htmlspecialchars($schedule_id) ?></td>
            <td><?= htmlspecialchars($schedule_info->release_at ?? '---') ?></td>
            <td><?= htmlspecialchars($schedule_info->revision ?? '---') ?></td>
            <td><a href="?a=proj.<?= htmlspecialchars($project_id ?? '') ?>.scheduler.<?= htmlspecialchars($schedule_id ?? '') ?>.detail" class="px2-btn px2-btn--primary">詳細</a></td>
        </tr>
        <?php
    }
    echo '</tbody>'."\n";
    echo '</table>'."\n";
    echo '</div>'."\n";
}
?>
