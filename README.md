# Onion Slice


## セットアップ - Setup

1. `dist/` ディレクトリ内に置かれている `onion-slice.php` を、管理用のウェブサーバーの任意のディレクトリにアップロードする。
2. ウェブブラウザで、アップロードした `onion-slice.php` にアクセスし、初期設定画面を開く。
3. 画面の指示に従って初期設定する。


### スケジュール更新機能のセットアップ

Onion Slice には、スケジュール更新機能が付属しています。
この機能を利用するには、ウェブフロントサーバーに、 `onion-slice--waiter.phar` をセットアップします。

1. `dist/` ディレクトリ内に置かれている `onion-slice--waiter.phar` を、ウェブフロントサーバーの任意のディレクトリ(非公開ディレクトリ)にアップロードする。
2. 任意の非公開ディレクトリに、環境設定ファイル(JSON)を作成する。

```json
{
    "api_endpoint": "http://192.168.0.12:8080/onion-slice.php",
    "api_key": "xxxxxxxxx",
    "api_basic_auth": "basic_auth_id:yourpassword",
    "realpath_data_dir": "/path/to/onion-slice--waiter_files/",
    "realpath_public_symlink": "/path/to/var/www/htdocs",
    "git_remote": "https://git-remote.com/example/example.git",
    "project_id": "xxxxxxxxxxxxxxxxx",
    "scripts": {
        "post-deploy-cmd": [
            "anycommand"
        ]
    },
    "commands": {
        "php": "/opt/homebrew/bin/php",
        "php": "/usr/local/bin/composer" ,
        "git": "/usr/bin/git"
    }
}
```

3. `crontab` に、次のようにコマンドを登録する。

```bash
* * * * * /path/to/onion-slice--waiter.phar --env /path/to/env.json
```



## 更新履歴 - Change log

### tomk79/app-onion-slice v0.3.1 (2025年4月3日)

- Git操作機能のエラー処理に関する改善。
- ファイルとフォルダ機能で、ダウンロードの処理を改善した。
- `onion-slice--waiter.phar` が、デプロイタスクがない場合にも `scripts.post-deploy-cmd` と `composer install` を実行する問題を修正した。

### tomk79/app-onion-slice v0.3.0 (2024年5月17日)

- `onion-slice--waiter.phar` の設定に `scripts.post-deploy-cmd` を追加。
- `onion-slice--waiter.phar` にPHPコマンドのパスが設定されていないときに、実行中のPHPからコマンドのパスを取得するようになった。
- スケジューラーで、リリーススケジュールを降順に並ぶように変更した。
- スケジューラーで、古い配信タスクをアーカイブするようになった。
- スケジューラーで、古いスタンバイを削除するようになった。
- スケジューラーで、配信予約を作成した時点のリビジョンの情報を記録するようになった。
- その他のUI改善、細かい不具合の修正など。

### tomk79/app-onion-slice v0.2.0 (2024年4月30日)

- スケジュール配信機能を追加。
- APIキーの管理機能を追加。
- ユーザーディレクトリ名を `admin_users` から `users` に改名した。
- ログと内部管理される時刻情報を ISO 8601 形式 に変更した。

### tomk79/app-onion-slice v0.1.0 (2023年8月29日)

- Initial Release



## ライセンス - License

MIT License https://opensource.org/licenses/mit-license.php


## 作者 - Author

- Tomoya Koyanagi <tomk79@gmail.com>
- website: <https://www.pxt.jp/>
- Twitter: @tomk79 <https://twitter.com/tomk79/>
