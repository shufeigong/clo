<?php

function foundationpress_scripts()
{
    // Deregister the jquery version bundled with WordPress.
    wp_deregister_script( 'jquery' );


    // Enqueue the main Stylesheet.
    wp_enqueue_style( 'main-stylesheet', get_stylesheet_directory_uri() . '/dist/css/app.css' );
//    wp_enqueue_style( 'foundation-stylesheet', get_template_directory_uri() . '/css/foundation.css' );
    if(!(isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'Trident') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)))
    {
    	wp_enqueue_style( 'ie-stylesheet', get_stylesheet_directory_uri() . '/dist/css/iestyle.css' );
    }
    
    // Modernizr is used for polyfills and feature detection. Must be placed in header. (Not required).
    wp_register_script( 'modernizr', get_template_directory_uri() . '/js/vendor/modernizr.js', array(), '2.8.3', false );

    // Fastclick removes the 300ms delay on click events in mobile environments. Must be placed in header. (Not required).
    wp_register_script( 'fastclick', get_template_directory_uri() . '/js/vendor/fastclick.js', array(), '1.0.0', false );


    // CDN hosted jQuery placed in the header, as some plugins require that jQuery is loaded in the header.
    wp_register_script( 'jquery', '//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js', array(), '2.1.1', false );

    wp_register_script( 'foundation', get_template_directory_uri() . '/js/foundation.js', array('jquery'), '5.5.2', true );
    
    

    // Enqueue all registered scripts.
    wp_enqueue_script( 'modernizr' );
    wp_enqueue_script( 'fastclick' );
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'foundation' );

    wp_enqueue_script( 'vendor', get_stylesheet_directory_uri() . '/dist/js/vendor.js', array(), '1.0.0', true );
    
    if(!is_user_logged_in()){
    	wp_enqueue_script( 'myheight', get_stylesheet_directory_uri() . '/src/js/heightres.js', array('jquery'), '1.0.0', true );
    }
    else{
    	wp_enqueue_script( 'myheightlogin', get_stylesheet_directory_uri() . '/src/js/heightreslogin.js', array('jquery'), '1.0.0', true );
    }
   
    
    wp_enqueue_script( 'myAppMenu', get_stylesheet_directory_uri() . '/src/js/menu.js', array('jquery'), '1.0.0', true );
    wp_enqueue_script( 'myMobileMenu', get_stylesheet_directory_uri() . '/src/js/mobileMenu.js', array('jquery'), '1.0.0', true );
    
   

    

    wp_enqueue_script( 'bundle', get_stylesheet_directory_uri() . '/dist/js/app.bundle.js', array('jquery'), '1.0.0', true );
}

add_action('wp_enqueue_scripts', 'foundationpress_scripts');

?>
