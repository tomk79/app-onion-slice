<?php
$projects = new \tomk79\onionSlice\model\projects($rencon);
$project_id = $rencon->get_route_param('projectId');
$project = $projects->get($project_id);
$scheduler = $project->scheduler();
?>


<p class="px2-text-align-right"><button type="button" class="px2-btn px2-btn--primary">新規配信予定</button></p>
<?php
$active_schedules = $scheduler->get_schedule_all();
if( !count($active_schedules) ){
    echo "<p>配信予定はありません。</p>";
}else{
    echo '<table class="px2-table">'."\n";
    foreach( $active_schedules as $schedule_id => $schedule_info ){
        ?>
        <tr>
            <td><?= htmlspecialchars($schedule_id) ?></td>
            <td><?= htmlspecialchars($schedule_info->release_at) ?></td>
            <td><?= htmlspecialchars(strtotime($schedule_info->release_at)) ?></td>
            <td><?= htmlspecialchars(date('Y-m-d H:i:s (e)', strtotime($schedule_info->release_at))) ?></td>
        </tr>
        <?php
    }
    echo '</table>'."\n";
}
?>
