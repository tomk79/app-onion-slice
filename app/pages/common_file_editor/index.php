<?php
$projects = new \tomk79\onionSlice\model\projects($rencon);
$project_id = $rencon->get_route_param('projectId');
$project_info = $projects->get_project($project_id);
?>

<?php if( !strlen($project_info->realpath_base_dir ?? '') || !is_dir($project_info->realpath_base_dir) ){ ?>
	<p>ベースディレクトリが存在しないか、設定されていません。</p>
<?php }else{ ?>

<div style="padding: 40px; margin: 30 auto; text-align: center;">
<p>汎用ファイルエディタ は開発中の機能です。</p>
</div>

<?php } ?>
