var gulp       = require('gulp'),
    browserify = require('browserify'),
    buffer     = require('vinyl-buffer'),
    reactify   = require('reactify'),
    bundler    = browserify('./src/components/picard.jsx'),
    source     = require('vinyl-source-stream');
bundler.transform(reactify);

gulp.task(
    'scripts', function () {
        return bundler.bundle()
            .pipe(source('picard.js'))
            //.pipe( buffer() )
            .pipe(gulp.dest('./dist/js'));
    }
);

gulp.task(
    'animation_scripts', function () {
        return
    }
);

