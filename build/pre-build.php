<?php

// --------------------------------------
// Remote Finder
$file_path = __DIR__.'/../resources/remote-finder/remote-finder.css';
$css_bin = file_get_contents($file_path);
$css_bin = preg_replace('/url\(\"images\//', 'url("?res=remote-finder/images/', $css_bin);
file_put_contents($file_path, $css_bin);
if( is_file( __DIR__.'/../resources/remote-finder/remote-finder.min.css' ) ){
    unlink( __DIR__.'/../resources/remote-finder/remote-finder.min.css' );
}
if( is_file( __DIR__.'/../resources/remote-finder/remote-finder.min.js' ) ){
    unlink( __DIR__.'/../resources/remote-finder/remote-finder.min.js' );
}


// --------------------------------------
// common-file-editor
if( is_file( __DIR__.'/../resources/common-file-editor/common-file-editor.min.css' ) ){
    unlink( __DIR__.'/../resources/common-file-editor/common-file-editor.min.css' );
}
if( is_file( __DIR__.'/../resources/common-file-editor/common-file-editor.min.js' ) ){
    unlink( __DIR__.'/../resources/common-file-editor/common-file-editor.min.js' );
}


// --------------------------------------
// gitui79.js
if( is_file( __DIR__.'/../resources/gitui79.js/gitui79.min.css' ) ){
    unlink( __DIR__.'/../resources/gitui79.js/gitui79.min.css' );
}
if( is_file( __DIR__.'/../resources/gitui79.js/gitui79.min.js' ) ){
    unlink( __DIR__.'/../resources/gitui79.js/gitui79.min.js' );
}
if( is_file( __DIR__.'/../resources/gitui79.js/gitui79.js.map' ) ){
    unlink( __DIR__.'/../resources/gitui79.js/gitui79.js.map' );
}
if( is_file( __DIR__.'/../resources/gitui79.js/gitui79.min.js.map' ) ){
    unlink( __DIR__.'/../resources/gitui79.js/gitui79.min.js.map' );
}


// --------------------------------------
// node-git-parser
if( is_file( __DIR__.'/../resources/node-git-parser/gitParse79.min.js' ) ){
    unlink( __DIR__.'/../resources/node-git-parser/gitParse79.min.js' );
}



sleep(1);
exit();
