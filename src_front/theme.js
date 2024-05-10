window.$ = require('jquery');
const OsWorldClock = require('./theme_files/includes/OsWorldClock.js');
require('px2style/dist/px2style.js');
window.addEventListener('load', function(){
    console.log('window.onload(): done.', window.px2style);
});
window.osWorldClock = new OsWorldClock();
