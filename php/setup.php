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
			ob_start();
			$this->step01();
			$html = ob_get_clean();
			echo $this->rencon->theme()->bind( $html );
			exit();
		}


		if( !$this->rencon->fs()->is_file($this->rencon->conf()->path_data_dir.'/project/composer.json') ){
			ob_start();
			$this->step02();
			$html = ob_get_clean();
			echo $this->rencon->theme()->bind( $html );
			exit();
		}

		if( !$this->rencon->fs()->is_dir($this->rencon->conf()->path_data_dir.'/project/.git/') ){
			ob_start();
			$this->step03();
			$html = ob_get_clean();
			echo $this->rencon->theme()->bind( $html );
			exit();
		}


		return true;
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
				<button type="submit" class="px2-btn px2-btn--primary">次へ</button>
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

			exec($this->rencon->conf()->commands->php.' '.$path_composer.' create-project pickles2/preset-get-start-pickles2 ./');

			chdir($current_dir);
			$this->reload();
			return;
		}
		?>
			<p>プロジェクトをセットアップします。</p>
			<form action="?" method="post">
				<input type="hidden" name="a" value="<?= $this->rencon->req()->get_param('a') ?>" />
				<input type="hidden" name="cmd" value="next" />
				<button type="submit" class="px2-btn px2-btn--primary">次へ</button>
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

			exec($this->rencon->conf()->commands->git.' init');
			exec($this->rencon->conf()->commands->git.' add ./');
			exec($this->rencon->conf()->commands->git.' commit -m "Initial commit."');

			chdir($current_dir);
			$this->reload();
			return;
		}
		?>
			<p>Gitを初期化します。</p>
			<form action="?" method="post">
				<input type="hidden" name="a" value="<?= $this->rencon->req()->get_param('a') ?>" />
				<input type="hidden" name="cmd" value="next" />
				<button type="submit" class="px2-btn px2-btn--primary">次へ</button>
			</form>
		<?php
		return;
	}

}
