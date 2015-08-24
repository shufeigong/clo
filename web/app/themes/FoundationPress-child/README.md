# FoundationPress Child Theme

<blockquote>This project is <strong>not tested</strong> with the latest Foundation Press theme and is need of collaborators / maintainers to help keep this project up to date with the parent theme changes. Create an issue to request being added as a collaborator.</blockquote>

This is a Wordpress child theme for the parent theme [FoundationPress](https://github.com/olefredrik/FoundationPress) by `olfredrik`.  This theme follows the same conventions and allows you to use Grunt and Bower to control and update dependencies and watch for your SASS changes. 

The purpose of the Foundation Press is that it should only contain the most essential, with no need to peel away unnecessary features. If you're looking for a starter theme with built-in shortcode plugins, fancypancy portfolio templates or whatnot, I'm afraid you have to look elsewhere. 

## Requirements

*You'll need to have the following items installed before continuing.*

  * [Node.js](http://nodejs.org): Use the installer provided on the NodeJS website.
  * [Grunt](http://gruntjs.com/): Run `[sudo] npm install -g grunt-cli`
  * [Bower](http://bower.io): Run `[sudo] npm install -g bower`

## Quickstart

```bash
cd my-wordpress-folder/wp-content/themes/
git clone git@github.com:thetrickster/FoundationPress-child.git
mv FoundationPress-child your-theme-name
cd your-theme-name
npm install && bower install
```

While you're working on your project, run:

`grunt` or `grunt watch` for livereload in browser.

And you're set!

Check for Foundation Updates? Run:
`foundation update`


## Stylesheet Folder Structure

  * `style.css`: Do not worry about this file. (For some reason) it's required by WordPress. All styling are handled in the Sass files described below
  * `scss/_settings.scss`: Original Foundation base settings are found here
  * `scss/_custom.scss`: Add your custom styling here
  * `css/app.css`: All Sass files are minified and compiled to this file

## Script Folder Strucutre
  
  * `bower_components/`: This is the source folder where all Foundation scripts are located. `foundation update` will check and update scripts in this folder
  * `js/`: jQuery, Modernizr and Foundation scripts are copied from `bower_components/` to this directory, where they are minified and concatinated and enqueued in WordPress
  * Please note that you must run `grunt` in your terminal for the scripts to be copied. See [Gruntfile.js](https://github.com/olefredrik/FoundationPress/blob/master/Gruntfile.js) for details

## How to get started with Foundation

* [Zurb Foundation Docs](http://foundation.zurb.com/docs/)

## Learn how to use WordPress

* [WordPress Codex](http://codex.wordpress.org/)

## FoundationPress Parent Theme

* [FoundationPress](https://github.com/olefredrik/FoundationPress) 

## Demo of FoundationPress Parent Theme

* [Clean FoundationPress install](http://foundationpress.olefredrik.com/)
