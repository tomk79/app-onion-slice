<?php
$app_info = $this->app_info();
$current_page_info = $this->get_current_page_info();

$projects = new \tomk79\onionSlice\model\projects($rencon);
$project_id = $rencon->get_route_param('projectId');
$project_info = $projects->get_project($project_id);

$action = $rencon->req()->get_param('a');
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta name="csrf-token" content="<?= htmlspecialchars($this->rencon->auth()->get_csrf_token()) ?>" />
<title><?= htmlspecialchars( $app_info->name ?? '' ) ?> | <?= htmlspecialchars( $current_page_info->title ?? '' ) ?></title>
<link rel="stylesheet" href="?res=theme.css" />
<script src="?res=theme.js"></script>
</head>
<body>

<?php if( $action == 'proj.'.$project_id.'.common_file_editor' ){ ?>

<style>
.theme-wrap,
.theme-wrap .contents {
    flex-grow: 100;
    flex-shrink: 0;

    display: flex;
    flex-direction: column;
    height: 100px;
}
</style>
<div class="theme-wrap">
	<div class="contents">
<?= $content ?>
	</div>
</div>


<?php }else{ ?>

<header class="theme-header">
	<div class="theme-header__id">
		<a href="?a="><?= htmlspecialchars( $app_info->name ?? '' ) ?></a>
	</div>
<?php if( strlen($project_id ?? '') ) { ?>
	<div class="theme-header__project-name">
		<span><?= htmlspecialchars($project_info->name ?? '---') ?></span>
	</div>
<?php } ?>
	<div class="theme-header__global-menu">
<?php if( $project_info ){ ?>
		<ul>
			<li><a href="?a=proj.<?= htmlspecialchars($project_id ?? '') ?>">Project</a></li>
			<?php if($this->rencon->utils->has_composer_json($project_id)) { ?><li><a href="?a=proj.<?= htmlspecialchars($project_id ?? '') ?>.composer">Composerを操作する</a></li><?php } ?>
			<?php if($this->rencon->utils->has_dot_git($project_id)) { ?><li><a href="?a=proj.<?= htmlspecialchars($project_id ?? '') ?>.git">Gitを操作する</a></li><?php } ?>
			<?php if($this->rencon->utils->base_dir_exists($project_id)) { ?><li><a href="?a=proj.<?= htmlspecialchars($project_id ?? '') ?>.files_and_folders">ファイルとフォルダ</a></li><?php } ?>
		</ul>
<?php } ?>
	</div>
</header>




<div class="theme-frame">

	<div class="theme-main-container">
		<div class="theme-main-container__header-info">
			<div class="theme-h1-container">
				<div class="theme-h1-container__heading">
					<h1><?= nl2br( htmlspecialchars( $current_page_info->title ?? '' ) ) ?></h1>
				</div>
			</div>
		</div>

		<div class="theme-main-container__body">
			<div class="contents">
<?= $content ?>
			</div>
		</div>

	</div>
</div>

<footer class="theme-footer">
	<div class="theme-footer__menu">
		<ul>
			<li><a href="?a=">ダッシュボード</a></li>
			<li><a href="?a=env_config">環境設定</a></li>
<?php if( $rencon->auth()->is_login_required() && $rencon->user()->is_login() ) { ?>
			<li><a href="?a=logout">ログアウト</a></li>
<?php } ?>
		</ul>
	</div>
</footer>


<script>
window.current = <?= var_export($action, true) ?> || 'home';
</script>

<?php } ?>

</body>
</html>
