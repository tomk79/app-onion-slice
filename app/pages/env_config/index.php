<?php

$data = new \tomk79\onionSlice\model\env_config( $rencon );


if( !strlen($rencon->req()->get_param('m')) ){
	$rencon->req()->set_param('git-remote', $data->git_remote);
	$rencon->req()->set_param('git-user-name', $data->git_user_name);
	$rencon->req()->set_param('git-password', $data->git_password);
}



if( $rencon->req()->get_param('m') == 'success' ){
?>

<p>保存しました。</p>
<p><button class="px2-btn" onclick="window.location.href='?a=<?= htmlspecialchars($rencon->req()->get_param('a')) ?>';">完了</button></p>

<?php
	return;
}


if( $rencon->req()->get_param('m') == 'save' ){

	$data->git_remote = $rencon->req()->get_param('input-git-remote');
	$data->git_user_name = $rencon->req()->get_param('input-git-user-name');
	if( strlen($rencon->req()->get_param('input-git-password')) ){
		$data->git_password = $rencon->req()->get_param('input-git-password');
	}
	$data->save();

	header("Location: ?a=".htmlspecialchars($rencon->req()->get_param('a')).'&m=success');
	exit;
}else{

?>

<form action="?a=<?= htmlspecialchars($rencon->req()->get_param('a')) ?>" method="post">
	<input type="hidden" name="m" value="save" />

	<!-- ID/PWのオートコンプリートを無効にするためのダミー入力欄 -->
	<input type="password" name="autocomplete-off" value="" style="position: absolute; visibility: hidden; top: -100px; left: -100px;" />


	<div class="px2-form-input-list">
		<ul class="px2-form-input-list__ul">
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-git-remote">Gitリモート URL</label></div>
				<div class="px2-form-input-list__input"><input type="text" id="input-git-remote" name="input-git-remote" value="<?= htmlspecialchars($rencon->req()->get_param('git-remote')) ?>" class="px2-input" /></div>
			</li>
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-git-user-name">Gitリモート ユーザー名</label></div>
				<div class="px2-form-input-list__input"><input type="text" id="input-git-user-name" name="input-git-user-name" value="<?= htmlspecialchars($rencon->req()->get_param('git-user-name')) ?>" class="px2-input" /></div>
			</li>
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="input-git-password">Gitリモート パスワード</label></div>
				<div class="px2-form-input-list__input"><input type="password" id="input-git-password" name="input-git-password" value="" class="px2-input" /></div>
			</li>
		</ul>
	</div>

	<p class="px2-text-align-center"><button class="px2-btn px2-btn--primary">保存する</button></p>
</form>

<?php
}
?>