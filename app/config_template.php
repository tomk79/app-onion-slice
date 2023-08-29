<?php

$conf = new \stdClass();


/* --------------------------------------
 * ログインユーザーのIDとパスワードの対
 * 
 * `$conf->users` に 登録されたユーザーが、ログインを許可されます。
 * ユーザーIDを キー に、sha1ハッシュ化されたパスワード文字列を 値 に持つ連想配列で設定してください。
 * ユーザーは、複数登録できます。
 */
$conf->users = array(
	// "admin" => sha1("admin"),
);


/* --------------------------------------
 * 非公開データディレクトリのパス
 */
$conf->realpath_private_data_dir = __DIR__.'/'.basename(__FILE__, '.php').'_files/';


/* --------------------------------------
 * コマンドのパス
 */
$conf->commands = (object) array();
$conf->commands->php = 'php';
$conf->commands->git = 'git';



?>