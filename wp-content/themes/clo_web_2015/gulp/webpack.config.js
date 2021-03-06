var path = require('path');
var webpack = require('webpack');
var node_modules_dir = path.resolve(__dirname, '../node_modules');
var bower_dir = path.resolve(__dirname, '../bower_components');

module.exports = {
    cache: true,
    debug: true,
    watch: false,
    devtool: 'eval',
    entry: {
        app: './src/js/app.js',
        vendor: ["resizeEnd", "lightBox", "artwl", "album", "imagesloaded", "imagefill", "TweenMax", "dotdotdot"]
    },
    output: {
        path: path.resolve(__dirname, "dist/js"),
        filename: '[name].bundle.js',
        chunkFilename: "[id].bundle.js"
    },
    module: {
        loaders: [
            {
                test: './src/js',
                exclude: [node_modules_dir],
                loader: ['babel-loader']
            }
        ]
    },
    resolve: {
        // you can now require('file') instead of require('file.js')
        extensions: ['', '.js', '.json'],
        alias: {
            "resizeEnd": "./src/js/vendor/jquery.resizeEnd.js",
            "lightBox": "./src/js/vendor/lightbox.min.js",
            "artwl": "./src/js/vendor/jquery.artwl.thickbox.js",
            "album": "./src/js/vendor/jquery.album.thickbox.js",
            "imagesloaded": "./src/js/vendor/imagesloaded.js",
            "imagefill": "./src/js/vendor/jquery-imagefill.js",
            "TweenMax": './bower_components/gsap/src/minified/TweenMax.min.js',
            "dotdotdot":"./src/js/vendor/jquery.dotdotdot.js",
            "fetch": './bower_components//fetch/fetch.js',
            "es6-promise": './bower_components/es6-promise/promise.min.js'
            	
        }
    },
    plugins: [
        new webpack.optimize.CommonsChunkPlugin('vendor', 'vendor.js')
    ]
};