<?php
namespace tomk79\onionSlice;

class setup {
	private $rencon;
	public function __construct( $rencon ){
		$this->rencon = $rencon;
	}

	/**
	 * セットアップを進行する
	 */
	public function wizard(){
		$conf = $this->rencon->conf();

		if( !$this->rencon->fs()->is_dir($this->rencon->conf()->path_data_dir) ){
			$this->step01();
			return false;
		}


		return true;
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
			header('Location: ?a='.urlencode($this->rencon->req()->get_param('a')));
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

}
