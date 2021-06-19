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



sleep(1);
exit();
