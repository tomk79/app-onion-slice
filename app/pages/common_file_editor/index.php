<?php
$projects = new \tomk79\onionSlice\model\projects($rencon);
$project_id = $rencon->get_route_param('projectId');
$project_info = $projects->get_project($project_id);

$filename = $rencon->req()->get_param('filename');
?>

<?php if( !strlen($project_info->realpath_base_dir ?? '') || !is_dir($project_info->realpath_base_dir) ){ ?>
	<p>ベースディレクトリが存在しないか、設定されていません。</p>
<?php }else{ ?>

<div id="cont-common-file-editor"></div>

<link rel="stylesheet" href="?res=common-file-editor/common-file-editor.css" />
<script src="?res=common-file-editor/common-file-editor.js"></script>
<script>
(function(){
	var project_id = <?= var_export($project_id, true) ?>;
	var filename = <?= var_export($filename, true) ?>;
	var commonFileEditor = new CommonFileEditor(
		document.getElementById('cont-common-file-editor'),
		{
			"lang": "ja",
			"read": function(filename, callback){ // required
				$.ajax({
					url: `?a=api.${project_id}.common_file_editor.gpi`,
					type: 'post',
					data: {
						'method': 'read',
						'filename': filename,
			            'CSRF_TOKEN': $('meta[name="csrf-token"]').attr('content'),
					},
				}).done(function(res) {
					if( !res.result ){
						console.error('Error:', res);
					}
					callback(res);
				}).fail(function() {
					alert('Errored');
				}).always(function() {
				});
			},
			"write": function(filename, base64, callback){ // required
				$.ajax({
					url: `?a=api.${project_id}.common_file_editor.gpi`,
					type: 'post',
					data: {
						'method': 'write',
						'filename': filename,
						'base64': base64,
			            'CSRF_TOKEN': $('meta[name="csrf-token"]').attr('content'),
					},
				}).done(function(res) {
					if( !res.result ){
						console.error('Error:', res);
					}
					callback(res);
				}).fail(function() {
					alert('Errored');
				}).always(function() {
				});
			},
			"onemptytab": function(){
				window.close();
			},
		}
	);

	commonFileEditor.init(function(){
		commonFileEditor.preview( filename );
		callback(true);
	});
})();
</script>

<?php } ?>
