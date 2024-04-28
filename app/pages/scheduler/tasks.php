<?php
$projects = new \tomk79\onionSlice\model\projects($rencon);
$project_id = $rencon->get_route_param('projectId');
$project = $projects->get($project_id);
$scheduler = $project->scheduler();
?>


<ul class="px2-horizontal-list px2-horizontal-list--right">
    <li><a href="?a=proj.<?= htmlspecialchars($project_id) ?>.scheduler" class="px2-btn">戻る</a></li>
</ul>
<?php
$active_tasks = $scheduler->get_task_all();
if( !count(array_keys(get_object_vars($active_tasks))) ){
    echo "<p>配信タスクはありません。</p>";
}else{
    echo '<div class="px2-responsive">'."\n";
    echo '<table class="px2-table" style="width:100%;">'."\n";
    ?>
    <thead>
    <tr>
        <th>タスクID</th>
        <th>操作</th>
        <th>リビジョン</th>
        <th></th>
    </tr>
    </thead>
    <?php
    echo '<tbody>'."\n";
    foreach( $active_tasks as $task_id => $task_info ){
        ?>
        <tr>
            <td><?= htmlspecialchars($task_id) ?></td>
            <td><?= htmlspecialchars($task_info->type ?? '---') ?></td>
            <td><?= htmlspecialchars($task_info->properties->revision ?? '---') ?></td>
            <td><a href="?a=proj.<?= htmlspecialchars($project_id ?? '') ?>.scheduler.tasks.<?= htmlspecialchars($task_id ?? '') ?>.detail" class="px2-btn px2-btn--primary">詳細</a></td>
        </tr>
        <?php
    }
    echo '</tbody>'."\n";
    echo '</table>'."\n";
    echo '</div>'."\n";
}
?>
