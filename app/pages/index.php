<p>ようこそ、Pickles 2</p>

<?php
$env_config = new \tomk79\onionSlice\model\env_config( $rencon );
$pickles2 = new \tomk79\onionSlice\helpers\pickles2( $rencon );
$px2proj = $pickles2->create_px2agent();
$px2all = $px2proj->query(
	'/?PX=px2dthelper.get.all',
	array(
		'output' => 'json'
	)
);
$realpath_entry_script = $pickles2->get_entry_script();
$realpath_publish_dir = $rencon->fs()->get_realpath('./'.$px2all->config->path_publish_dir, dirname($realpath_entry_script));

?>


<h2>プレビューURL</h2>
<p><a href="<?= htmlspecialchars( ($env_config->url_preview ?? '').($px2all->config->path_controot ?? '') ) ?>" target="_blank"><?= htmlspecialchars( $env_config->url_preview ?? '' ) ?></a></p>
<p>次のパスに割り当ててください。</p>
<pre><code><?= htmlspecialchars( $px2all->realpath_docroot ?? '' ) ?></code></pre>

<h2>本番URL</h2>
<p><a href="<?= htmlspecialchars( ($env_config->url_production ?? '').($px2all->config->path_controot ?? '') ) ?>" target="_blank"><?= htmlspecialchars( $env_config->url_production ?? '' ) ?></a></p>
<p>次のパスに割り当ててください。</p>
<pre><code><?= htmlspecialchars( $realpath_publish_dir ?? '' ) ?></code></pre>
