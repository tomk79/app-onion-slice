window.$ = require('jquery');
window.px2style = new (require('px2style'))();
window.addEventListener('load', function(){
    console.log('window.onload(): done.', window.px2style);
	window.px2style.header.init({'current':window.current});
});
