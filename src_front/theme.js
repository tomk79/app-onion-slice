window.$ = require('jquery');
require('px2style/dist/px2style.js');
window.addEventListener('load', function(){
    console.log('window.onload(): done.', window.px2style);
	window.px2style.header.init({'current':window.current});
});
