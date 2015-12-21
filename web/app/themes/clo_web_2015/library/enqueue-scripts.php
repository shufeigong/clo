<?php

function foundationpress_scripts()
{
    // Deregister the jquery version bundled with WordPress.
    wp_deregister_script('jquery');

    // Enqueue the main Stylesheet.
    wp_enqueue_style('main-stylesheet', get_stylesheet_directory_uri() . '/dist/css/app.css');
    //    wp_enqueue_style( 'foundation-stylesheet', get_template_directory_uri() . '/css/foundation.css' );

    $userAgent = parse_user_agent();

    if ($userAgent['browser'] == 'MSIE') {//style sheet support MSIE
        wp_enqueue_style('ie-stylesheet', get_stylesheet_directory_uri() . '/dist/css/iestyle.css');
    }
    
    if ($userAgent['browser'] == 'Firefox') {//style sheet support Firefox
    	wp_enqueue_style('firefox-stylesheet', get_stylesheet_directory_uri() . '/dist/css/firefoxstyle.css');
    }
    
    if ($userAgent['browser'] == 'Safari') {//style sheet support safari
    	wp_enqueue_style('safari-stylesheet', get_stylesheet_directory_uri() . '/dist/css/safaristyle.css');
    	if((int)$userAgent['version'][0] < 9){
    		wp_enqueue_style('safari9-stylesheet', get_stylesheet_directory_uri() . '/dist/css/safari9style.css');
    	}
    }

    
    // Since IE9 doesn't support pushState, we need a polyfill for that
    if ($userAgent['browser'] == 'MSIE' && $userAgent['version'] == 9) {
        wp_enqueue_script('ie-history', get_stylesheet_directory_uri() . '/src/js/vendor/history.js');
    }

    // Modernizr is used for polyfills and feature detection. Must be placed in header. (Not required).
    wp_register_script('modernizr', get_template_directory_uri() . '/js/vendor/modernizr.js', array(), '2.8.3', true);

    // Fastclick removes the 300ms delay on click events in mobile environments. Must be placed in header. (Not required).
    wp_register_script('fastclick', get_template_directory_uri() . '/js/vendor/fastclick.js', array(), '1.0.0', true);


    // CDN hosted jQuery placed in the header, as some plugins require that jQuery is loaded in the header.
    wp_register_script('jquery', '//cdn.jsdelivr.net/jquery/2.1.1/jquery.min.js', array(), '2.1.1', true);

//    wp_register_script(
//        'foundation',
//        get_template_directory_uri() . '/js/foundation.js',
//        array('jquery'),
//        '5.5.2',
//        true
//    );

    // Enqueue all registered scripts.
    wp_enqueue_script('modernizr');
    wp_enqueue_script('fastclick');
    wp_enqueue_script('jquery');
//    wp_enqueue_script('foundation');

    wp_enqueue_script('foundation', get_stylesheet_directory_uri() . '/dist/js/foundation.js', array(), '5.5.2', true);


    wp_enqueue_script('vendor', get_stylesheet_directory_uri() . '/dist/js/vendor.js', array(), '1.0.0', true);
    if (!is_user_logged_in()) {
        wp_enqueue_script(
            'myheight',
            get_stylesheet_directory_uri() . '/src/js/heightres.js',
            array('jquery'),
            '1.0.0',
            true
        );
    } else {
        wp_enqueue_script(
            'myheightlogin',
            get_stylesheet_directory_uri() . '/src/js/heightreslogin.js',
            array('jquery'),
            '1.0.0',
            true
        );
    }

    wp_enqueue_script('myAppMenu', get_stylesheet_directory_uri() . '/src/js/menu.js', array('jquery'), '1.0.0', true);
    wp_enqueue_script(
        'myMobileMenu',
        get_stylesheet_directory_uri() . '/src/js/mobileMenu.js',
        array('jquery'),
        '1.0.0',
        true
    );

        wp_enqueue_script( 'bundle', get_stylesheet_directory_uri() . '/dist/js/app.bundle.js', array('jquery'), '1.0.0', true );
}

add_action('wp_enqueue_scripts', 'foundationpress_scripts');

?>
