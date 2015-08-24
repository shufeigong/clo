var gulp   = require('gulp'),
    jshint = require('gulp-jshint'),
    stylish      = require('jshint-stylish');

// Jshint outputs any kind of javascript problems you might have
// Only checks javascript files inside /js directory
gulp.task(
    'jshint', function () {
        return gulp.src('./src/js/**/*.js')
            .pipe(jshint('.jshintrc'))
            .pipe(jshint.reporter(stylish))
            .pipe(jshint.reporter('fail'));
    }
);
