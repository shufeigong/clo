<?php
/**
 * The template for displaying the header
 *
 * Displays all of the head element and everything up until the "container" div.
 *
 * @package WordPress
 * @subpackage FoundationPress
 * @since FoundationPress 1.0.0
 */
?>
<!doctype html>
<!--[if IE 9 ]>
<html lang="en" class="no-js ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!-->
<html <?php language_attributes(); ?> ><!--<![endif]-->
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    
    
    <link rel="icon" href="<?php echo get_stylesheet_directory_uri(); ?>/dist/img/icons/favicon.ico"
          type="image/x-icon">
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="<?php echo get_stylesheet_directory_uri(
    ); ?>/dist/img/icons/apple-touch-icon-144x144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="<?php echo get_stylesheet_directory_uri(
    ); ?>/dist/img/icons/apple-touch-icon-114x114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72"
          href="<?php echo get_stylesheet_directory_uri(); ?>/dist/img/icons/apple-touch-icon-72x72-precomposed.png">
    <link rel="apple-touch-icon-precomposed"
          href="<?php echo get_stylesheet_directory_uri(); ?>/dist/img/icons/apple-touch-icon-precomposed.png">
    
    <?php wp_head(); ?>
    <meta property="og:image" content="http://clo-web-2015.gotenzing.com/app/uploads/2015/10/CLO_Website_Pull.jpg" />
</head>
<body <?php body_class(); ?>>
<!-- Google Tag Manager -->
<noscript><iframe src="//www.googletagmanager.com/ns.html?id=GTM-M8NN8X"
                  height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        '//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-M8NN8X');</script>
<!-- End Google Tag Manager -->
<?php do_action('foundationpress_after_body'); ?>
<?php

$userAgent = parse_user_agent();

if ($userAgent['browser'] == 'MSIE' && (int)$userAgent['version'] < 11) {
    echo '<p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/?locale=en/">upgrade your browser</a> to improve your experience.</p>';

} ?>
<div class="off-canvas-wrap" data-offcanvas>
    <div class="inner-wrap">
       
     <?php get_template_part('parts/off-canvas-menu'); ?>

<section class="container" role="document">
<?php do_action('foundationpress_after_header'); ?>
<div class="row">

     <?php if(is_user_logged_in()):?>
    <div class="row header login">
     <?php else:?>
    <div class="row header non-login">
     <?php endif;?>

	        <nav class="tab-bar show-for-small-only">
	            <section class="right-small">
	                <a class="right-off-canvas-toggle menu-icon" ><span></span></a>
	            </section>
	        </nav>
    
        <div class="columns large-4 medium-4 small-12 small-logo">
        <?php if(ICL_LANGUAGE_CODE=='en'): ?>
            <a class="site-logo" href="<?php bloginfo('url'); ?>" title="<?php bloginfo('name'); ?>" style="text-indent:100%;white-space:nowrap;overflow:hidden;">
                <img src="<?php echo get_stylesheet_directory_uri(); ?>/dist/img/clo_logo.svg" alt="Website Logo"/>
            </a>
        <?php elseif(ICL_LANGUAGE_CODE=='fr'): ?>
            <a class="site-logo-french" href="<?php bloginfo('url'); ?>" title="<?php bloginfo('name'); ?>" style="text-indent:100%;white-space:nowrap;overflow:hidden;">
                <img src="<?php echo get_stylesheet_directory_uri(); ?>/dist/img/clo_logo_french.svg" alt="Website Logo French version"/>
            </a>
         <?php endif;?>   
        </div>
        <div class="columns large-8 medium-4 small-8 small-utility">
            <?php get_template_part('parts/utility-menu'); ?>
        </div>
        <?php get_search_form(); ?>
	         <div class="columns large-4 medium-4 small-12 mbl-img">
	             <img src="<?php echo get_stylesheet_directory_uri(); ?>/dist/img/mbl-img.svg" alt="mbl-img"/>
	         </div>
<?php //$userAgent = parse_user_agent(); echo $userAgent['browser'].'////'.$userAgent['version'][0];?>
             <div class="columns large-4 medium-4 small-12 mbx-dh">
                 <?php  
                        $menu_breadcrumb = new Menu_Breadcrumb( 'main-menu' );   // 'main' is the Menu Location
                        $menu_breadcrumb2 = new Menu_Breadcrumb( 'utility-menu' );   // 'utility' is the Menu Location
                        
                        $current_menu_item_object = $menu_breadcrumb->get_current_menu_item_object();
                        $current_menu_item_object2 = $menu_breadcrumb2->get_current_menu_item_object();
                        if($current_menu_item_object){
                        	$breadcrumb_array = $menu_breadcrumb->generate_trail();
                            $breadcrumb_markup = $menu_breadcrumb->generate_markup( $breadcrumb_array, ' > ' );
                            if(ICL_LANGUAGE_CODE=='en')
                                echo '<p class="menu-breadcrumb"><a id="gohome">HOME</a> > ' . $breadcrumb_markup . '</p>';
                            else
                            	echo '<p class="menu-breadcrumb"><a id="gohome">Accueil</a> > ' . $breadcrumb_markup . '</p>';
                        } else if($current_menu_item_object2){
                        	$breadcrumb_array = $menu_breadcrumb2->generate_trail();
                        	$breadcrumb_markup = $menu_breadcrumb2->generate_markup( $breadcrumb_array, ' > ' );
                        	if(ICL_LANGUAGE_CODE=='en')
                        	    echo '<p class="menu-breadcrumb"><a id="gohome">HOME</a> > ' . $breadcrumb_markup . '</p>';
                        	else
                        		echo '<p class="menu-breadcrumb"><a id="gohome">Accueil</a> > ' . $breadcrumb_markup . '</p>';
                        	
                        }else {
                        	if(ICL_LANGUAGE_CODE=='en')
                        	    echo '<a id="gohome">HOME</a>';
                        	else
                        	    echo '<a id="gohome">Accueil</a>';
                        }
                        
                        
                  ?>     
             </div>
    </div>
