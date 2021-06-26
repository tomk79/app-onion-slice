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
			<p>Onion Slice のセットアップへようこそ！</p>
			<p>はじめに、データディレクトリを作成します。</p>
			<p>データディレクトリのパスは次の通りです。</p>
			<pre><code><?= htmlspecialchars( $this->rencon->fs()->get_realpath($this->rencon->conf()->path_data_dir) ) ?></code></pre>
			<p>よろしければ、「次へ」をクリックしてください。</p>
			<form action="?" method="post">
				<input type="hidden" name="a" value="<?= $this->rencon->req()->get_param('a') ?>" />
				<input type="hidden" name="cmd" value="next" />
				<p><button type="submit" class="px2-btn px2-btn--primary">次へ</button></p>
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

			if( $this->rencon->req()->get_param('setup-option') == 'pickles2' ){
				// --------------------------------------
				// preset-get-start-pickles2 からセットアップ
				chdir($base_dir);
				exec($this->rencon->conf()->commands->php.' '.$path_composer.' create-project pickles2/preset-get-start-pickles2 ./');
				chdir($current_dir);

			}elseif( $this->rencon->req()->get_param('setup-option') == 'git' ){
				// --------------------------------------
				// 任意のGitリモートからセットアップ
				$gitremote = $this->rencon->req()->get_param('git-url');
				$username = $this->rencon->req()->get_param('git-username');
				$password = $this->rencon->req()->get_param('git-password');
				$gitHelper = new \tomk79\onionSlice\helpers\git( $this->rencon );
				$gitHelper->git( array(
					'clone',
					$gitHelper->url_bind_confidentials($gitremote, $username, $password),
					'./',
				) );
				chdir($base_dir);
				exec($this->rencon->conf()->commands->php.' '.$path_composer.' install');
				chdir($current_dir);

				$env_config = new \tomk79\onionSlice\model\env_config( $this->rencon );
				$env_config->git_url = $this->rencon->req()->get_param('git-url');
				$env_config->git_username = $this->rencon->req()->get_param('git-username');
				$env_config->git_password = $this->rencon->req()->get_param('git-password');
				$env_config->save();
			}

			$this->reload();
			return;
		}
		?>
			<p>続いて、プロジェクトをセットアップします。</p>
			<form action="?" method="post">
				<ul>
					<li><label><input type="radio" name="setup-option" value="pickles2" checked="checked" /> Packagist から Pickles 2 プロジェクトテンプレート をセットアップ</label></li>
					<li><label><input type="radio" name="setup-option" value="git" /> Gitリポジトリ から クローン</label>
						<table style="width: 100%;">
							<tr>
								<th>Repository URL</th>
								<td><input type="text" name="git-url" value="" class="px2-input px2-input--block" /></td>
							</tr>
							<tr>
								<th>User name</th>
								<td><input type="text" name="git-username" value="" class="px2-input px2-input--block" /></td>
							</tr>
							<tr>
								<th>Password</th>
								<td><input type="password" name="git-password" value="" class="px2-input px2-input--block" /></td>
							</tr>
						</table>
					</li>
				</ul>
				<input type="hidden" name="a" value="<?= $this->rencon->req()->get_param('a') ?>" />
				<input type="hidden" name="cmd" value="next" />
				<p><button type="submit" class="px2-btn px2-btn--primary">次へ</button></p>
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
				<p><button type="submit" class="px2-btn px2-btn--primary">次へ</button></p>
			</form>
		<?php
		return;
	}

}
