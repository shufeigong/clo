var gulp = require('gulp');

// The default task. When developting just run 'gulp' and this is what will be ran.
// Note the second parameter, those are dependency tasks which need to be done
// before the main function (third parameter) is called.
gulp.task(
    'build', ['images', 'styles', 'scripts'], function () {
        console.log('done');
    }
);