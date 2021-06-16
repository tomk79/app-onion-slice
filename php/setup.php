<?php
namespace tomk79\onionSlice;

class setup {
	private $rencon;


	/**
	 * Constructor
	 */
	public function __construct( $rencon ){
		$this->rencon = $rencon;
	}


	/**
	 * セットアップを進行する
	 */
	public function wizard(){
		$conf = $this->rencon->conf();

		if( !$this->rencon->fs()->is_dir($this->rencon->conf()->path_data_dir) ||
			!$this->rencon->fs()->is_dir($this->rencon->conf()->path_data_dir.'/project/') ||
			!$this->rencon->fs()->is_file($this->rencon->conf()->path_data_dir.'/commands/composer/composer.phar') ){
			$this->step01();
			return false;
		}


		if( !$this->rencon->fs()->is_file($this->rencon->conf()->path_data_dir.'/project/composer.json') ){
			$this->step02();
			return false;
		}

		$path_entry_script = $this->get_entry_script();

		$px2agent = new \picklesFramework2\px2agent\px2agent();
		$px2proj = $px2agent->createProject( $this->rencon->conf()->path_data_dir.'/project/'.$path_entry_script );


		if( !$this->rencon->fs()->is_dir($this->rencon->conf()->path_data_dir.'/project/.git/') ){
			$this->step03();
			return false;
		}


		return true;
	}


	/**
	 * entryScriptのパスを調べる
	 */
	private function get_entry_script(){
		if( !$this->rencon->fs()->is_file($this->rencon->conf()->path_data_dir.'/project/composer.json') ){
			return false;
		}

		$path_entry_script = '.px_execute.php';

		$src_composer_json = $this->rencon->fs()->read_file( $this->rencon->conf()->path_data_dir.'/project/composer.json' );
		$composer_json = json_decode( $src_composer_json );

		if( !isset( $composer_json->extra->px2package ) ){
			return $path_entry_script;
		}

		if( is_object($composer_json->extra->px2package) && isset($composer_json->extra->px2package->path) ){
			$path_entry_script = $composer_json->extra->px2package->path;
		}elseif( is_array($composer_json->extra->px2package) ){
			foreach( $composer_json->extra->px2package as $row ){
				if( is_object($row) && isset($row->path) ){
					if( isset($row->type) && $row->type != 'project' ){
						continue;
					}
					$path_entry_script = $row->path;
					break;
				}
			}
		}

		return $path_entry_script;
	}

	/**
	 * reload
	 */
	private function reload(){
		header('Location: ?a='.urlencode($this->rencon->req()->get_param('a')));
		return;
	}



	/**
	 * ステップ1: データディレクトリを作成する
	 */
	private function step01(){
		if( $this->rencon->req()->get_param('cmd') == 'next' ){
			if( !$this->rencon->fs()->mkdir($this->rencon->conf()->path_data_dir) ){
				?>
				<p>ディレクトリの作成に失敗しました。</p>
				<?php
				return;
			}
			if( !$this->rencon->fs()->mkdir($this->rencon->conf()->path_data_dir.'/project/') ){
				?>
				<p>ディレクトリの作成に失敗しました。</p>
				<?php
				return;
			}
			if( !$this->rencon->fs()->mkdir_r($this->rencon->conf()->path_data_dir.'/commands/composer/') ){
				?>
				<p>ディレクトリの作成に失敗しました。</p>
				<?php
				return;
			}
			$bin = $this->rencon->resources()->get('resources/composer.phar');
			$this->rencon->fs()->save_file( $this->rencon->conf()->path_data_dir.'/commands/composer/composer.phar', $bin );
			$this->reload();
			return;
		}
		?>
			<p>データディレクトリを作成します。</p>
			<form action="?" method="post">
				<input type="hidden" name="a" value="<?= $this->rencon->req()->get_param('a') ?>" />
				<input type="hidden" name="cmd" value="next" />
				<button type="submit">次へ</button>
			</form>
		<?php
		return;
	}

	/**
	 * ステップ2: プロジェクトをセットアップ
	 */
	private function step02(){
		if( $this->rencon->req()->get_param('cmd') == 'next' ){
			$path_composer = realpath($this->rencon->conf()->path_data_dir.'/commands/composer/composer.phar');
			$base_dir = $this->rencon->conf()->path_data_dir.'/project/';
			$current_dir = realpath('.');
			chdir($base_dir);

			exec('php '.$path_composer.' create-project pickles2/preset-get-start-pickles2 ./');

			chdir($current_dir);
			$this->reload();
			return;
		}
		?>
			<p>プロジェクトをセットアップします。</p>
			<form action="?" method="post">
				<input type="hidden" name="a" value="<?= $this->rencon->req()->get_param('a') ?>" />
				<input type="hidden" name="cmd" value="next" />
				<button type="submit">次へ</button>
			</form>
		<?php
		return;
	}

	/**
	 * ステップ3: Gitを初期化します
	 */
	private function step03(){
		if( $this->rencon->req()->get_param('cmd') == 'next' ){
			$path_composer = realpath($this->rencon->conf()->path_data_dir.'/commands/composer/composer.phar');
			$base_dir = $this->rencon->conf()->path_data_dir.'/project/';
			$current_dir = realpath('.');
			chdir($base_dir);

			exec('git init');
			exec('git add ./');
			exec('git commit -m "Initial commit."');

			chdir($current_dir);
			$this->reload();
			return;
		}
		?>
			<p>Gitを初期化します。</p>
			<form action="?" method="post">
				<input type="hidden" name="a" value="<?= $this->rencon->req()->get_param('a') ?>" />
				<input type="hidden" name="cmd" value="next" />
				<button type="submit">次へ</button>
			</form>
		<?php
		return;
	}

}
