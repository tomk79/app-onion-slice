<?php
$projects = new \tomk79\onionSlice\model\projects($rencon);
$project_id = $rencon->get_route_param('projectId');
$project_info = $projects->get_project($project_id);
?>

<div style="padding: 40px; margin: 30 auto; text-align: center;">
<p>汎用ファイルエディタ は開発中の機能です。</p>
</div>
