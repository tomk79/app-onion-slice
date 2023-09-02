$(window).on('load', function(){
	const $release_at = $('input[name=input-release_at]');
	const $releaseDate = $('input[name=input-release_at-date]');
	const $releaseHour = $('select[name=input-release_at-hour]');
	const $releaseMin = $('select[name=input-release_at-min]');

	(function(){
		// 初期化
		const timestamp = parseInt($release_at.val()) * 1000;
		const date = new Date(timestamp);
		$releaseDate.val(
			date.getUTCFullYear() + '-'
			+ (date.getUTCMonth()+1).toString().padStart(2, '0') + '-'
			+ date.getUTCDate().toString().padStart(2, '0')
		);
		$releaseHour.val(date.getUTCHours());
		$releaseMin.val(date.getUTCMinutes());
	})();


	// ユーザーの入力を反映する
	function onChange(){
		const date = new Date(
			$releaseDate.val() + 'T'
			+ $releaseHour.val().toString().padStart(2, '0') + ':'
			+ $releaseMin.val().toString().padStart(2, '0') + ':'
			+ '00'
			+ 'Z'
		);
		$release_at.val( date.getTime() / 1000 );
	}

	$releaseDate.on('change', onChange);
	$releaseHour.on('change', onChange);
	$releaseMin.on('change', onChange);
});