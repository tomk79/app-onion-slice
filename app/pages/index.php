<div class="px2-p">
	<div class="px2-text-align-right"><a href="?a=proj_create" class="px2-btn px2-btn--primary">新規作成</a></div>
</div>
<div class="px2-p">
	<table class="px2-table" style="width:100%;">
		<tbody>
<?php
$projects = new \tomk79\onionSlice\model\projects($rencon);
foreach( $projects->get_projects() as $project_id => $project_info ){ ?>
			<tr>
				<td><a href="?a=proj.<?= htmlspecialchars($project_id ?? '') ?>"><?= htmlspecialchars( $project_info->name ?? '---' ) ?></a></td>
				<td></td>
			</tr>
<?php } ?>
		</tbody>
	</table>
</div>
