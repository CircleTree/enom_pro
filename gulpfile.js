/**
 * Gulp workflow containing tasks and watchers for Enom Pro module.
 *
 * Utilizes gulp-run to execute shell command to build default phing
 * target local and gulp-livereload to refresh page once build finishes.
 */
var gulp = require('gulp'),
	run = require('gulp-run'),
	livereload = require('gulp-livereload');

/**
 * Project source directory
 */
var src = './src';

/**
 * Task uses ssh to login into local development pv and auto
 * executes phing local target build inside of enom_pro directory.
 * Pipes results through livereload to refresh page.
 */
gulp.task('run-phing', function () {
	run("ssh vagrant@127.0.0.1 -p 2222 'cd enom_pro; vendor/bin/phing;'").exec()
		.on('error', onError)
		.pipe(livereload());
});

/**
 * Task to watch src directory for any changes refreshing page with livereload.
 * If changes are detected, executes 'run-phing' task to rebuild project.
 */
gulp.task('watch', function () {
	livereload.listen();
	gulp.watch(src + '/**/*', ['run-phing']);
});

/**
 * Default gulp task runs when executing 'gulp'
 */
gulp.task('default', ['watch', 'run-phing']);

/**
 * Error handler that keeps gulp from crashing when it encounters an error.
 */
function onError(err) {
	console.log(err);
	this.emit('end');
}