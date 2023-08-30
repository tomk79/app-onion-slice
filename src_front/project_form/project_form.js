$(window).on('load', function(){
	$('input[name=input-type]')
		.on('change', function(){
			var $radios = $('input[name=input-type]:checked');
			var value = $radios.val();
			if( value == 'directory' ){
				$('input[name="input-realpath_base_dir"]').closest('.px2-form-input-list__li').show();
				$('select[name="input-remote"]').closest('.px2-form-input-list__li').show();
				$('select[name="input-staging"]').closest('.px2-form-input-list__li').hide();
			}else if( value == 'scheduler' ){
				$('input[name="input-realpath_base_dir"]').closest('.px2-form-input-list__li').hide();
				$('select[name="input-remote"]').closest('.px2-form-input-list__li').hide();
				$('select[name="input-staging"]').closest('.px2-form-input-list__li').show();
			}
		})
		.trigger('change');
});