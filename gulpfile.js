/**
 * Gulp workflow containing tasks and watchers for Enom Pro module.
 *
 * Utilizes gulp-run to execute shell command to build default phing
 * target local and gulp-livereload to refresh page once build finishes.
 */
var gulp = require('gulp'),
	run = require('gulp-run'),
	wait = require('gulp-wait'),
	less = require('gulp-less'),
	livereload = require('gulp-livereload');

/**
 * Project source directory
 */
var src = './src',
	src_css = src + '/modules/addons/enom_pro/css';

/**
 * Task uses ssh to login into local development pv and auto
 * executes phing local target build inside of enom_pro directory.
 */
gulp.task('run-phing', function () {
	return run("ssh vagrant@127.0.0.1 -p 2222 'cd enom_pro; vendor/bin/phing;'")
		.exec()
		.on('error', onError)
		.pipe(wait(4000))
		.pipe(livereload());
});

gulp.task('less', function () {
	return gulp.src(src_css + '/admin.less')
		.pipe(less({
			paths: [src_css]
		}))
		.pipe(gulp.dest(src_css));
});

/**
 * Task to watch src directory for any changes refreshing page with livereload.
 * If changes are detected, executes 'run-phing' task to rebuild project.
 */
gulp.task('watch', function () {
	livereload.listen();
	gulp.watch(src + '/**/*', ['run-phing']);
	gulp.watch(src_css + '/admin.less', ['less']);
	return;
});

/**
 * Default gulp task runs when executing 'gulp'
 */
gulp.task('default', ['watch', 'less', 'run-phing']);

/**
 * Error handler that keeps gulp from crashing when it encounters an error.
 */
function onError(err) {
	console.log(err);
	this.emit('end');
	return;
}
