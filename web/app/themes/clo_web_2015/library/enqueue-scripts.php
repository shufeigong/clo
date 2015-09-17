<?php
function foundationpress_scripts()
{
    // Deregister the jquery version bundled with WordPress.
    wp_deregister_script( 'jquery' );

    // Enqueue the main Stylesheet.
    wp_enqueue_style( 'main-stylesheet', get_stylesheet_directory_uri() . '/dist/css/app.css' );
//    wp_enqueue_style( 'foundation-stylesheet', get_template_directory_uri() . '/css/foundation.css' );

    // Modernizr is used for polyfills and feature detection. Must be placed in header. (Not required).
    wp_register_script( 'modernizr', get_template_directory_uri() . '/js/vendor/modernizr.js', array(), '2.8.3', false );

    // Fastclick removes the 300ms delay on click events in mobile environments. Must be placed in header. (Not required).
    wp_register_script( 'fastclick', get_template_directory_uri() . '/js/vendor/fastclick.js', array(), '1.0.0', false );

    // CDN hosted jQuery placed in the header, as some plugins require that jQuery is loaded in the header.
    wp_register_script( 'jquery', '//ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js', array(), '2.1.0', false );

    // If you'd like to cherry-pick the foundation components you need in your project, head over to Gruntfile.js and see lines 67-88.
    // It's a good idea to do this, performance-wise. No need to load everything if you're just going to use the grid anyway, you know :)
    wp_register_script( 'foundation', get_template_directory_uri() . '/js/foundation.js', array('jquery'), '5.5.2', true );

    // Enqueue all registered scripts.
    wp_enqueue_script( 'modernizr' );
    wp_enqueue_script( 'fastclick' );
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'foundation' );

//    wp_enqueue_script('react', get_stylesheet_directory_uri() . '/dist/js/picard.js', array(), '', true);
//    wp_enqueue_script( 'rwdImage', get_stylesheet_directory_uri() . '/src/js/jquery.rwdImageMaps.min.js', array('jquery'), '1.0.0', true );
    wp_enqueue_script( 'app', get_stylesheet_directory_uri() . '/src/js/app.js', array('jquery'), '1.0.0', true );
    wp_enqueue_script( 'myheight', get_stylesheet_directory_uri() . '/src/js/heightres.js', array('jquery'), '1.0.0', true );
    wp_enqueue_script( 'myAppMenu', get_stylesheet_directory_uri() . '/src/js/menu.js', array('jquery'), '1.0.0', true );
    wp_enqueue_script( 'myMobileMenu', get_stylesheet_directory_uri() . '/src/js/mobileMenu.js', array('jquery'), '1.0.0', true );
    
}

add_action('wp_enqueue_scripts', 'foundationpress_scripts');

?>
