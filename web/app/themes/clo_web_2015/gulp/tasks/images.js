var gulp     = require('gulp'),
    imagemin = require('gulp-imagemin'),
    pngcrush = require('imagemin-pngcrush');

// == IMAGE TASKS == //
// = Any images in the src/img folder are minified then copied over to the dist/img folder = //
gulp.task(
    'images', function () {
        return gulp.src('./src/img/**/*')
            .pipe(
            imagemin(
                {
                    progressive: true,
                    multipass: true,
                    svgoPlugins: [{removeViewBox: false}],
                    use: [pngcrush()]
                }
            )
        )
            .pipe(gulp.dest('./dist/img/'));
    }
);
