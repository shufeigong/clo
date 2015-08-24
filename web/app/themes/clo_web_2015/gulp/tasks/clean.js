var gulp = require('gulp'),
    clean = require('gulp-clean');

// == Clean Tasks == //
gulp.task(
    'clean', function () {
        return gulp.src([
            'dist/tmp/',
            'dist/js/*.js',
            'dist/css/*.css',
            'dist/img/*'
        ], {read: false})
            .pipe(clean());
    }
);
