var gulp         = require('gulp'),
    rename       = require('gulp-rename'),
    sass         = require('gulp-sass'),
    plumber      = require('gulp-plumber'),
    autoprefixer = require('gulp-autoprefixer'),
    minifycss    = require('gulp-minify-css'),
    browserSync  = require('browser-sync'),
    reload       = browserSync.reload;

// Processes SASS and reloads browser.
gulp.task(
    'styles', function () {
        return gulp.src('./src/scss/*.scss')
            .pipe(plumber())
            .pipe(sass({style: 'expanded'}))
            .pipe(autoprefixer('last 2 version', 'safari 5', 'ie 8', 'ie 9', 'opera 12.1'))
            .pipe(minifycss())
            .pipe(gulp.dest('./dist/css'))
            .pipe(reload({stream: true}));
    }
);