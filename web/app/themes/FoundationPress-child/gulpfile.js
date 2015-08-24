// 引入 gulp
var gulp = require('gulp'); 

// 引入组件
var jshint = require('gulp-jshint');
var sass = require('gulp-sass');
var concat = require('gulp-concat');
var uglify = require('gulp-uglify');
var rename = require('gulp-rename');

// 编译Sass
gulp.task('sass', function() {
    gulp.src('./scss/app.scss')
        .pipe(sass())
        .pipe(rename('app.css'))
        .pipe(gulp.dest('./css'));
});


// 默认任务
gulp.task('default', function(){
    gulp.run('sass');
});