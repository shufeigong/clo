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
<html class="no-js" <?php language_attributes(); ?> >
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <link rel="icon" href="<?php echo get_stylesheet_directory_uri(); ?>/src/img/icons/favicon.ico"
          type="image/x-icon">
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="<?php echo get_stylesheet_directory_uri(
    ); ?>/src/img/icons/apple-touch-icon-144x144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="<?php echo get_stylesheet_directory_uri(
    ); ?>/src/img/icons/apple-touch-icon-114x114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72"
          href="<?php echo get_stylesheet_directory_uri(); ?>/src/img/icons/apple-touch-icon-72x72-precomposed.png">
    <link rel="apple-touch-icon-precomposed"
          href="<?php echo get_stylesheet_directory_uri(); ?>/src/img/icons/apple-touch-icon-precomposed.png">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php do_action('foundationpress_after_body'); ?>

<div class="off-canvas-wrap" data-offcanvas>
    <div class="inner-wrap">
        <nav class="tab-bar show-for-small-only">
            <section class="right-small">
                <a class="right-off-canvas-toggle menu-icon" ><span></span></a>
            </section>
        </nav>

     <?php get_template_part('parts/off-canvas-menu'); ?>
     
<!--        -->
     
 

<section class="container" role="document">
<?php do_action('foundationpress_after_header'); ?>
<div class="row ">
    <div class="row header">
        <div class="columns large-4 medium-4 small-12 small-logo">
            <a class="site-logo" href="<?php bloginfo('url'); ?>" title="<?php bloginfo('name'); ?>">

<!--                <img class="" alt="Community Living Ontario Logo" src="--><?php //echo get_stylesheet_directory_uri(); ?><!--/dist/img/clo_logo.svg" />-->
<!--                <img class="show-for-medium-only" alt="Community Living Ontario Logo" src="--><?php //echo get_stylesheet_directory_uri(); ?><!--/dist/img/clo_logo_medium.svg" />-->
            </a>
        </div>
        <div class="columns large-8 medium-4 small-8 small-utility">
            <?php get_template_part('parts/utility-menu'); ?>
        </div>
        <?php get_search_form(); ?>
    </div>
