<?php
$projects = new \tomk79\onionSlice\model\projects($rencon);
$project_id = $rencon->get_route_param('projectId');
$project_info = $projects->get_project($project_id);
?>

<?php if( !strlen($project_info->realpath_base_dir ?? '') || !is_dir($project_info->realpath_base_dir) ){ ?>
	<p>ベースディレクトリが存在しないか、設定されていません。</p>
<?php }else{ ?>

<div id="cont-files-and-folders"></div>


<link rel="stylesheet" href="?res=remote-finder/remote-finder.css" />
<style>
#cont-files-and-folders {
	height: 500px;
}
</style>
<script src="?res=remote-finder/remote-finder.js"></script>
<script>

var remoteFinder = window.remoteFinder = new RemoteFinder(
	document.getElementById('cont-files-and-folders'),
	{
		"gpiBridge": function(input, callback){
			// console.log('===== GPI Bridge:', input, callback, $);
			var rtn = false;
			$.ajax({
				url : "?a=api.<?= htmlspecialchars( $project_id ?? '' ) ?>.remote_finder.gpi",
				headers: {
				},
				dataType: 'json',
				method : 'post',
				data: {
					'gpi_param': JSON.stringify(input),
		            'CSRF_TOKEN': $('meta[name="csrf-token"]').attr('content'),
				},
				success: function(data){
					// px2style.closeLoading();
					// console.log('---- GPI Bridge:', data);
					callback(data);
				},
				error: function(err){
					console.error(err);
				}
			});
		},
		"open": function(fileinfo, callback){
			px2style.loading();

			switch( fileinfo.ext ){
				default:
					var url = '?a=proj.<?= htmlspecialchars( $project_id ?? '' ) ?>.common_file_editor&filename='+encodeURIComponent(fileinfo.path);
					window.open(url);
					break;
			}
			px2style.closeLoading();
			callback(true);
		},
		"mkdir": function(current_dir, callback){
			// --------------------------------------
			// 新規フォルダの作成
			var $body = $('<div>').html( $('#template-mkdir').html() );
			$body.find('.cont_current_dir').text(current_dir);
			$body.find('[name=dirname]').on('change keyup', function(){
				var dirname = $body.find('[name=dirname]').val();
				if( dirname.match(/\.html?$/i) ){
					$body.find('.cont_html_ext_option').show();
				}else{
					$body.find('.cont_html_ext_option').hide();
				}
			});
			px2style.modal({
				'title': 'Create new Directory',
				'body': $body,
				'buttons': [
					$('<button type="button" class="px2-btn">')
						.text('Cancel')
						.on('click', function(e){
							px2style.closeModal();
						}),
					$('<button class="px2-btn px2-btn--primary">')
						.text('OK')
				],
				'form': {
					'submit': function(){
						px2style.closeModal();
						var dirname = $body.find('[name=dirname]').val();
						if( !dirname ){ return; }

						callback( dirname );
					}
				},
				'width': 460
			}, function(){
				$body.find('[name=dirname]').focus();
			});
		},
		"mkfile": function(current_dir, callback){
			// --------------------------------------
			// 新規ファイルの作成
			px2style.loading();

			var $body = $('<div>').html( $('#template-mkfile').html() );
			var pxExternalPath_before;
			var pathType_before;
			var pxExternalPath;
			var pathType;
			var pageInfoAll_before;
			new Promise(function(rlv){rlv();})
				.then(function(){ return new Promise(function(rlv, rjt){
					$body.find('.cont_current_dir').text(current_dir);
					$body.find('[name=filename]').on('change keyup', function(){
						var filename = $body.find('[name=filename]').val();
						if( pxExternalPath_before && pathType_before == 'contents' && filename.match(/\.html?$/i) ){
							$body.find('.cont_html_ext_option').show();
						}else{
							$body.find('.cont_html_ext_option').hide();
						}
					});
					rlv();
					return;
				}); })
				.then(function(){ return new Promise(function(rlv, rjt){
					px2style.closeLoading();

					px2style.modal({
						'title': 'Create new File',
						'body': $body,
						'buttons': [
							$('<button type="button" class="px2-btn">')
								.text('Cancel')
								.on('click', function(e){
									px2style.closeModal();
								}),
							$('<button class="px2-btn px2-btn--primary">')
								.text('OK')
						],
						'form': {
							'submit': function(){
								px2style.closeModal();
								var filename = $body.find('[name=filename]').val();
								if( !filename ){ return; }
								var pageInfoAll;

								px2style.loading();

								new Promise(function(rlv){rlv();})
									.then(function(){ return new Promise(function(rlv, rjt){
										px2style.closeLoading();
										callback( filename );
										rlv();
										return;
									}); })
								;

							}
						},
						'width': 460
					}, function(){
						$body.find('[name=filename]').focus();
					});
					rlv();
					return;
				}); })
			;

		},
		"copy": function(copyFrom, callback){
			// --------------------------------------
			// ファイルまたはフォルダの複製
			px2style.loading();

			var is_file;
			var pxExternalPathFrom;
			var pathFilesFrom;
			var pathTypeFrom;
			var pageInfoAllFrom;
			var pxExternalPathTo;
			var pathFilesTo;
			var pathTypeTo;
			var pageInfoAllTo;
			new Promise(function(rlv){rlv();})
				.then(function(){ return new Promise(function(rlv, rjt){
					fs('is_file', copyFrom, {}, function(result){
						is_file = result.result;
						rlv();
					});
					return;
				}); })
				.then(function(){ return new Promise(function(rlv, rjt){
					var $body = $('<div>').html( $('#template-copy').html() );
					$body.find('.cont_target_item').text(copyFrom);
					$body.find('[name=copy_to]').val(copyFrom);
					if(is_file && pxExternalPathFrom && pathTypeFrom == 'contents'){
						$body.find('.cont_contents_option').show();
					}else{
						$body.find('.cont_contents_option').hide();
					}
					px2style.closeLoading();

					px2style.modal({
						'title': 'Copy',
						'body': $body,
						'buttons': [
							$('<button type="button" class="px2-btn">')
								.text('Cancel')
								.on('click', function(e){
									px2style.closeModal();
								}),
							$('<button class="px2-btn px2-btn--primary">')
								.text('複製する')
						],
						'form': {
							'submit': function(){
								px2style.closeModal();
								var copyTo = $body.find('[name=copy_to]').val();
								if( !copyTo ){ return; }
								if( copyTo == copyFrom ){ return; }

								px2style.loading();
								new Promise(function(rlv){rlv();})
									.then(function(){ return new Promise(function(rlv, rjt){
										px2style.closeLoading();
										callback(copyFrom, copyTo);
										rlv();
										return;
									}); })
								;
							}
						},
						'width': 460
					}, function(){
						$body.find('[name=copy_to]').focus();
					});
					rlv();
					return;
				}); })
			;
		},
		"rename": function(renameFrom, callback){
			// --------------------------------------
			// ファイルまたはフォルダの移動・改名
			px2style.loading();

			var is_file;
			var pxExternalPathFrom;
			var pathFilesFrom;
			var pathTypeFrom;
			var pxExternalPathTo;
			var pathFilesTo;
			var pathTypeTo;
			new Promise(function(rlv){rlv();})
				.then(function(){ return new Promise(function(rlv, rjt){
					fs('is_file', renameFrom, {}, function(result){
						is_file = result.result;
						rlv();
					});
					return;
				}); })
				.then(function(){ return new Promise(function(rlv, rjt){
					var $body = $('<div>').html( $('#template-rename').html() );
					$body.find('.cont_target_item').text(renameFrom);
					$body.find('[name=rename_to]').val(renameFrom);
					if(is_file && pxExternalPathFrom && pathTypeFrom == 'contents'){
						$body.find('.cont_contents_option').show();
					}else{
						$body.find('.cont_contents_option').hide();
					}

					px2style.closeLoading();

					px2style.modal({
						'title': 'Rename',
						'body': $body,
						'buttons': [
							$('<button type="button" class="px2-btn">')
								.text('Cancel')
								.on('click', function(e){
									px2style.closeModal();
								}),
							$('<button class="px2-btn px2-btn--primary">')
								.text('移動する')
						],
						'form': {
							'submit': function(){
								px2style.closeModal();
								var renameTo = $body.find('[name=rename_to]').val();
								if( !renameTo ){ return; }
								if( renameTo == renameFrom ){ return; }

								px2style.loading();

								new Promise(function(rlv){rlv();})
									.then(function(){ return new Promise(function(rlv, rjt){
										px2style.closeLoading();
										callback(renameFrom, renameTo);
										rlv();
										return;
									}); })
								;
							}
						},
						'width': 460
					}, function(){
						$body.find('[name=rename_to]').focus();
					});
					rlv();
					return;
				}); })
			;
		},
		"remove": function(target_item, callback){
			// --------------------------------------
			// ファイルまたはフォルダの削除

			px2style.loading();

			var is_file;
			var pageInfoAll;
			var pxExternalPath;
			var pathFiles;
			var pathType;
			new Promise(function(rlv){rlv();})
				.then(function(){ return new Promise(function(rlv, rjt){
					fs('is_file', target_item, {}, function(result){
						is_file = result.result;
						rlv();
					});
					return;
				}); })
				.then(function(){ return new Promise(function(rlv, rjt){
					var $body = $('<div>').html( $('#template-remove').html() );
					$body.find('.cont_target_item').text(target_item);
					if(is_file && pxExternalPath && pathType == 'contents'){
						$body.find('.cont_contents_option').show();
					}

					px2style.closeLoading();

					px2style.modal({
						'title': 'Remove',
						'body': $body,
						'buttons': [
							$('<button type="button" class="px2-btn">')
								.text('Cancel')
								.on('click', function(e){
									px2style.closeModal();
								}),
							$('<button class="px2-btn px2-btn--danger">')
								.text('削除する')
						],
						'form': {
							'submit': function(){
								px2style.closeModal();

								px2style.loading();

								new Promise(function(rlv){rlv();})
									.then(function(){ return new Promise(function(rlv, rjt){
										if( is_file && pxExternalPath && pathType == 'contents' && $body.find('[name=is_remove_files_too]:checked').val() && pathFiles.length ){
											// リソースも一緒に削除する
											fs('is_dir', pathFiles, {}, function(result){
												if(result.result){
													fs('remove', pathFiles, {}, function(result){
														rlv();
													});
													return;
												}
												rlv();
											});
											return;
										}
										rlv();
										return;
									}); })
									.then(function(){ return new Promise(function(rlv, rjt){
										px2style.closeLoading();
										callback();
										rlv();
										return;
									}); })
								;
							}
						},
						'width': 460
					}, function(){
					});
					rlv();
					return;
				}); })
			;
		},
		"generateDownloadLink": function(targetFile, callback){
			callback("?a=api.<?= htmlspecialchars( $project_id ?? '' ) ?>.remote_finder.download&path=" + encodeURIComponent(targetFile));
		},
	}
);
remoteFinder.init('/', {}, function(){
	console.log('ready.');
});


function fs(method, filename, options, callback){
	callback = callback || function(){};
	$.ajax({
		url : '?a=api.<?= htmlspecialchars( $project_id ?? '' ) ?>.common_file_editor.gpi',
		headers: {
		},
		dataType: 'json',
        method: "post",
		data: {
			'method': method,
			'filename': filename,
			'to': options.to,
			'px_command': options.px_command,
			'bin': options.bin,
            'CSRF_TOKEN': $('meta[name="csrf-token"]').attr('content'),
		},
		error: function(err){
			console.error(err);
		},
		success: function(data){
			callback(data);
		}
	});

}

</script>


		<!-- Template: mkfile dialog -->
		<script id="template-mkfile" type="text/template">
<p>Current Directory</p>
<div>
	<pre class="cont_current_dir"></pre>
</div>
<p>File name</p>
<div>
	<p><input type="text" name="filename" value="" class="form-control" /></p>
</div>
<div class="cont_html_ext_option" style="display: none;">
	<p>GUI編集モード</p>
	<div>
		<p><label><input type="checkbox" name="is_guieditor" value="1" checked="checked" /> GUI編集モードを有効にする</label></p>
	</div>
</div>
		</script>

		<!-- Template: mkdir dialog -->
		<script id="template-mkdir" type="text/template">
<p>Current Directory</p>
<div>
	<pre class="cont_current_dir"></pre>
</div>
<p>Directory name</p>
<div>
	<p><input type="text" name="dirname" value="" class="form-control" /></p>
</div>
		</script>

		<!-- Template: copy dialog -->
		<script id="template-copy" type="text/template">
<p>Target item</p>
<div>
	<pre class="cont_target_item"></pre>
</div>
<p>New file name</p>
<div>
	<p><input type="text" name="copy_to" value="" class="form-control" /></p>
</div>
<div class="cont_contents_option" style="display: none;">
	<div>
		<p><label><input type="checkbox" name="is_copy_files_too" value="1" checked="checked" /> リソースファイルもあわせて複製する</label></p>
	</div>
</div>
		</script>

		<!-- Template: rename dialog -->
		<script id="template-rename" type="text/template">
<p>Target item</p>
<div>
	<pre class="cont_target_item"></pre>
</div>
<p>New file name</p>
<div>
	<p><input type="text" name="rename_to" value="" class="form-control" /></p>
</div>
<div class="cont_contents_option" style="display: none;">
	<div>
		<p><label><input type="checkbox" name="is_rename_files_too" value="1" checked="checked" /> リソースファイルもあわせて移動する</label></p>
	</div>
</div>
		</script>

		<!-- Template: remove dialog -->
		<script id="template-remove" type="text/template">
<p>本当に削除してよろしいですか？</p>
<div>
	<pre class="cont_target_item"></pre>
</div>
<div class="cont_contents_option" style="display: none;">
	<div>
		<p><label><input type="checkbox" name="is_remove_files_too" value="1" checked="checked" /> リソースファイルもあわせて削除する</label></p>
	</div>
</div>
		</script>

<?php } ?>
