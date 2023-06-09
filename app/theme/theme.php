<?php
$app_info = $this->app_info();
$current_page_info = $this->get_current_page_info();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1 maximum-scale=1" />
<meta name="csrf-token" content="<?= htmlspecialchars($this->rencon->auth()->get_csrf_token()) ?>" />
<title><?= htmlspecialchars( $app_info->name ?? '' ) ?> | <?= htmlspecialchars( $current_page_info->title ?? '' ) ?></title>
<link rel="stylesheet" href="?res=theme.css" />
<script src="?res=theme.js"></script>
</head>
<body>

<?php if( $rencon->req()->get_param('a') == 'contents_editor' || $rencon->req()->get_param('a') == 'common_file_editor' ){ ?>


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
		<span><?= htmlspecialchars( $app_info->name ?? '' ) ?></span>
	</div>
	<div class="theme-header__global-menu">
		<ul>
			<li><a href="?a=" data-name="home">ホーム</a></li>
			<li><a href="?a=env_config" data-name="env_config">環境設定</a></li>
			<li><a href="?a=composer" data-name="composer">Composerを操作する</a></li>
			<li><a href="?a=git" data-name="git">Gitを操作する</a></li>
			<li><a href="?a=files_and_folders" data-name="files_and_folders">ファイルとフォルダ</a></li>
<?php if( $rencon->auth()->is_login_required() && $rencon->user()->is_login() ) { ?>
			<li><a href="?a=logout" data-name="quit">Logout</a></li>
<?php } ?>
		</ul>
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


<script>
window.current = <?= var_export($rencon->req()->get_param('a'), true) ?> || 'home';
</script>

<?php } ?>

</body>
</html>
