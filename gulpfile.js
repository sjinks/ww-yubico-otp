'use strict';

const gulp         = require('gulp');
const del          = require('del');
const rename       = require('gulp-rename');
const sourcemaps   = require('gulp-sourcemaps');
const uglify       = require('gulp-uglify');
const newer        = require('gulp-newer');
const wppot        = require('gulp-wp-pot');

gulp.task('clean:js', function() {
	return del(['assets/*.min.js', 'assets/*.js.map']);
});

gulp.task('clean', gulp.series(['clean:js']));

gulp.task('pot:front', function() {
	return gulp.src(['inc/*.php', 'views/*.php'])
		.pipe(wppot({
			domain: 'ww-yubiotp-front',
			headers: false,
			'package': 'WW YubiKey OTP'
		}))
		.pipe(gulp.dest('lang/ww-yubiotp-front.pot'))
	;
});

gulp.task('pot:admin', function() {
	return gulp.src(['inc/*.php', 'views/*.php'])
		.pipe(wppot({
			domain: 'ww-yubiotp-admin',
			headers: false,
			'package': 'WW YubiKey OTP'
		}))
		.pipe(gulp.dest('lang/ww-yubiotp-admin.pot'))
	;
});

gulp.task('pot', gulp.parallel(['pot:admin', 'pot:front']));

gulp.task('js', function() {
	var src  = ['assets/*.js', '!assets/*.min.js'];
	var dest = 'assets/';
	return gulp.src(src)
		.pipe(newer({
			dest: dest,
			ext: '.min.js'
		}))
		.pipe(sourcemaps.init())
		.pipe(uglify())
		.pipe(rename({suffix: '.min'}))
		.pipe(sourcemaps.write('.'))
		.pipe(gulp.dest(dest))
	;
});

gulp.task('default', gulp.parallel(['js', 'pot']));
