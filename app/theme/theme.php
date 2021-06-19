<?php
$app_info = $this->app_info();
$current_page_info = $this->get_current_page_info();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<title><?= htmlspecialchars( $app_info->name ) ?> | <?= htmlspecialchars( $current_page_info->title ) ?></title>
<link rel="stylesheet" href="?res=bootstrap5/css/bootstrap.css" />
<link rel="stylesheet" href="?res=theme.css" />
<script src="?res=bootstrap5/js/bootstrap.js"></script>
<script src="?res=theme.js"></script>
</head>
<body>

<header class="px2-header">
	<div class="px2-header__inner">
		<div class="px2-header__px2logo">
			<a href="?a="><img src="?res=logo.svg" alt="" /></a>
		</div>
		<div class="px2-header__block">
			<div class="px2-header__id">
				<span><?= htmlspecialchars( $app_info->name ) ?></span>
			</div>
			<div class="px2-header__global-menu">
				<ul>
					<li><a href="?a=" data-name="">HOME</a></li>
					<li><a href="?a=sitemaps" data-name="sitemaps">Sitemaps</a></li>
					<li><a href="?a=themes" data-name="themes">Themes</a></li>
					<li><a href="?a=contents" data-name="contents">Contents</a></li>
					<li><a href="?a=publish" data-name="publish">Publish</a></li>
				</ul>
			</div>
		</div>
		<div class="px2-header__shoulder-menu">
			<button><span class="px2-header__hamburger"></span></button>
			<ul>
				<li><a href="?a=clearcache" data-name="clearcache">キャッシュを消去する</a></li>
<?php if( $rencon->conf()->is_login_required() && $rencon->user()->is_login() ) { ?>
				<li><a href="?a=logout" data-name="quit">Logout</a></li>
<?php } ?>
			</ul>
		</div>
	</div>
</header>



<div class="theme-middle">
<h1><?= nl2br( htmlspecialchars( $current_page_info->title ) ) ?></h1>
<div class="contents">
<?= $content ?>
</div>
</div>


<footer class="theme-footer">
<div class="theme-footer__inner">
<?php if( $rencon->conf()->is_login_required() && $rencon->user()->is_login() ) { ?>
<p>
	<a href="?a=logout">Logout</a>
</p>
<?php } ?>
</div>
</footer>

<script>
window.current = <?= var_export($rencon->req()->get_param('a'), true) ?>;
</script>
</body>
</html>
