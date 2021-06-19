<div id="cont-files-and-folders"></div>


<link rel="stylesheet" href="?res=remote-finder/remote-finder.css" />
<script src="?res=remote-finder/remote-finder.js"></script>
<script>

var remoteFinder = window.remoteFinder = new RemoteFinder(
    document.getElementById('cont-files-and-folders'),
    {
        "gpiBridge": function(input, callback){
            // console.log('===== GPI Bridge:', input, callback, $);
            var rtn = false;
            $.ajax({
                type : 'get',
                url : "?a=api.remote_finder.gpi",
                headers: {
                },
                contentType: 'application/json',
                dataType: 'json',
                data: {
                    'gpi_param': JSON.stringify(input)
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
        // "open": function(fileinfo, callback){
        //     alert('ファイル ' + fileinfo.path + ' を開きました。');
        //     callback(true);
        // },
        // "mkdir": function(current_dir, callback){
        //     var foldername = prompt('Folder name:');
        //     if( !foldername ){ return; }
        //     callback( foldername );
        //     return;
        // },
        // "mkfile": function(current_dir, callback){
        //     var filename = prompt('File name:');
        //     if( !filename ){ return; }
        //     callback( filename );
        //     return;
        // },
        // "rename": function(renameFrom, callback){
        //     var renameTo = prompt('Rename from '+renameFrom+' to:', renameFrom);
        //     callback( renameFrom, renameTo );
        //     return;
        // },
        // "remove": function(path_target, callback){
        //     if( !confirm('Really?') ){
        //         return;
        //     }
        //     callback();
        //     return;
        // },
        // "mkdir": function(current_dir, callback){
        //     var foldername = prompt('Folder name:');
        //     if( !foldername ){ return; }
        //     callback( foldername );
        //     return;
        // },
        // "mkdir": function(current_dir, callback){
        //     var foldername = prompt('Folder name:');
        //     if( !foldername ){ return; }
        //     callback( foldername );
        //     return;
        // }
    }
);
// console.log(remoteFinder);
remoteFinder.init('/', {}, function(){
    console.log('ready.');
});

</script>