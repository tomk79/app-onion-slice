<?php
$projects = new \tomk79\onionSlice\model\projects($rencon);
$project_id = $rencon->get_route_param('projectId');
$project = $projects->get($project_id);
$scheduler = $project->scheduler();
?>
<p>配信スケジュールを登録します。</p>
