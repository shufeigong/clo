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
<html  <?php language_attributes(); ?> >
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
</head>
<body <?php body_class(); ?>>
<?php do_action('foundationpress_after_body'); ?>

<div class="off-canvas-wrap" data-offcanvas>
    <div class="inner-wrap">
       
     <?php get_template_part('parts/off-canvas-menu'); ?>

<section class="container" role="document">
<?php do_action('foundationpress_after_header'); ?>
<div class="row ">

     <?php if(is_user_logged_in()):?>
    <div class="row header login">
     <?php else:?>
    <div class="row header non-login">
     <?php endif;?>
     
       <?php //get_template_part('parts/off-canvas-menu'); ?>
	        
	        <nav class="tab-bar show-for-small-only">
	            <section class="right-small">
	                <a class="right-off-canvas-toggle menu-icon" ><span></span></a>
	            </section>
	        </nav>
    
        <div class="columns large-4 medium-4 small-12 small-logo">
        <?php if(ICL_LANGUAGE_CODE=='en'): ?>
            <a class="site-logo" href="<?php bloginfo('url'); ?>" title="<?php bloginfo('name'); ?>"></a>
        <?php elseif(ICL_LANGUAGE_CODE=='fr'): ?>
        <a class="site-logo-french" href="<?php bloginfo('url'); ?>" title="<?php bloginfo('name'); ?>"></a>
         <?php endif;?>   
        </div>
        <div class="columns large-8 medium-4 small-8 small-utility">
            <?php get_template_part('parts/utility-menu'); ?>
        </div>
        <?php get_search_form(); ?>
        
        
	         <div class="mbl-img">
	             <img src="<?php echo get_stylesheet_directory_uri(); ?>/dist/img/mbl-img.svg"/>
	         </div>

             <div class="mbx-dh">
                 <?php  
                        $menu_breadcrumb = new Menu_Breadcrumb( 'main-menu' );   // 'main' is the Menu Location
                        
                        $current_menu_item_object = $menu_breadcrumb->get_current_menu_item_object();
                        if($current_menu_item_object){
                        	
                        	$breadcrumb_array = $menu_breadcrumb->generate_trail();
                            $breadcrumb_markup = $menu_breadcrumb->generate_markup( $breadcrumb_array, ' > ' );
                            echo '<p class="menu-breadcrumb"><a id="gohome">HOME</a> > ' . $breadcrumb_markup . '</p>';
                        } else {
                        	echo '<a id="gohome">HOME</a>';
                        }
                  ?>     
             </div>
    </div>
