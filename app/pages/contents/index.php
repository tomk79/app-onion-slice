<p>コンテンツ は開発中の機能です。</p>

<?php

$pickles2 = new \tomk79\onionSlice\helpers\pickles2($rencon);
$px2proj = $pickles2->create_px2agent();
$sitemap = $px2proj->query(
	'/?PX=api.get.sitemap',
	array(
		"output" => "json",
	)
);



echo '<ul>';
foreach( $sitemap as $pid=>$page_info ){
echo '<li><a href="?a=contents_editor&page_path='.htmlspecialchars(urlencode($page_info->path)).'" target="_blank">'.htmlspecialchars($page_info->title).'</a></li>';
}
echo '</ul>';

?>