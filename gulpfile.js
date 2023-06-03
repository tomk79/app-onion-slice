let gulp = require('gulp');
let webpack = require('webpack');
let webpackStream = require('webpack-stream');
let sass = require('gulp-sass');//CSSコンパイラ
let autoprefixer = require("gulp-autoprefixer");//CSSにベンダープレフィックスを付与してくれる
let minifyCss = require('gulp-minify-css');//CSSファイルの圧縮ツール
let uglify = require("gulp-uglify");//JavaScriptファイルの圧縮ツール
let concat = require('gulp-concat');//ファイルの結合ツール
let plumber = require("gulp-plumber");//コンパイルエラーが起きても watch を抜けないようになる
let rename = require("gulp-rename");//ファイル名の置き換えを行う
let packageJson = require(__dirname+'/package.json');


// client-libs:remote-finder
gulp.task("client-libs:remote-finder", function(callback) {
	return gulp.src(["vendor/tomk79/remote-finder/dist/**/*"])
		.pipe(gulp.dest( './resources/remote-finder/' ))
	;
	callback();
});

// client-libs:common-file-editor
gulp.task("client-libs:common-file-editor", function(callback) {
	return gulp.src(["node_modules/@tomk79/common-file-editor/dist/**/*"])
		.pipe(gulp.dest( './resources/common-file-editor/' ))
	;
	callback();
});

// client-libs:gitui79.js
gulp.task("client-libs:gitui79.js", function(callback) {
	return gulp.src(["node_modules/gitui79/dist/**/*"])
		.pipe(gulp.dest( './resources/gitui79.js/' ))
	;
	callback();
});

// client-libs:node-git-parser
gulp.task("client-libs:node-git-parser", function(callback) {
	return gulp.src(["node_modules/gitparse79/dist/**/*"])
		.pipe(gulp.dest( './resources/node-git-parser/' ))
	;
	callback();
});

// src 中の *.css.scss を処理
gulp.task('.css.scss', function(){
	return gulp.src("src_front/**/*.css.scss")
		.pipe(plumber())
		.pipe(sass({
			"sourceComments": false
		}))
		.pipe(autoprefixer())
		.pipe(rename({
			extname: ''
		}))
		.pipe(rename({
			extname: '.css'
		}))
		.pipe(gulp.dest( './resources/' ))

		// .pipe(minifyCss({compatibility: 'ie8'}))
		// .pipe(rename({
		// 	extname: '.min.css'
		// }))
		// .pipe(gulp.dest( './resources/' ))
	;
});

// theme.js (frontend) を処理
gulp.task("theme.js", function() {
	return webpackStream({
		mode: 'production',
		entry: "./src_front/theme.js",
		output: {
			filename: "theme.js"
		},
		module:{
			rules:[
				{
					test:/\.html$/,
					use:['html-loader']
				}
			]
		}
	}, webpack)
		.pipe(plumber())
		.pipe(gulp.dest( './resources/' ))
	;
});

// ブラウザを立ち上げてプレビューする
gulp.task("preview", function(callback) {
	require('child_process').spawn('open',['http://127.0.0.1:3000/']);
	callback();
});



let _tasks = gulp.parallel(
	'client-libs:remote-finder',
	'client-libs:common-file-editor',
	'client-libs:gitui79.js',
	'client-libs:node-git-parser',
	'theme.js',
	'.css.scss'
);

// src 中のすべての拡張子を監視して処理
gulp.task("watch", function() {
	return gulp.watch(["src_front/**/*"], _tasks);
});

// src 中のすべての拡張子を処理(default)
gulp.task("default", _tasks);
