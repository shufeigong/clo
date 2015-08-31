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

    <link rel="icon" href="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/icons/favicon.ico"
          type="image/x-icon">
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="<?php echo get_stylesheet_directory_uri(
    ); ?>/assets/img/icons/apple-touch-icon-144x144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="<?php echo get_stylesheet_directory_uri(
    ); ?>/assets/img/icons/apple-touch-icon-114x114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72"
          href="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/icons/apple-touch-icon-72x72-precomposed.png">
    <link rel="apple-touch-icon-precomposed"
          href="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/icons/apple-touch-icon-precomposed.png">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php do_action('foundationpress_after_body'); ?>

<div class="off-canvas-wrap" data-offcanvas>
    <div class="inner-wrap">
        <?php do_action('foundationpress_layout_start'); ?>

        <?php get_template_part('parts/top-bar'); ?>

        <section class="container" role="document">
            <?php do_action('foundationpress_after_header'); ?>
            <div class="row ">
                <div class="row header">
                    <div class="columns large-4 medium-4 small-12">
                        <a class="site-logo" href="<?php bloginfo('url'); ?>" title="<?php bloginfo('name'); ?>">
                            <img alt="Community Living Ontario Logo"
                                 src="<?php echo get_stylesheet_directory_uri(); ?>/dist/img/clo_logo.svg" />
                        </a>
                    </div>
                    <div class="columns large-8 medium-4 small-8">
                        <?php
                        $topbarinfo = array(
                            'theme_location'  => '',
                            'menu'            => 'topbar',
                            'container'       => 'div',
                            'container_class' => '',
                            'container_id'    => '',
                            'menu_class'      => 'menu',
                            'menu_id'         => '',
                            'echo'            => true,
                            'fallback_cb'     => 'wp_page_menu',
                            'before'          => '',
                            'after'           => '',
                            'link_before'     => '',
                            'link_after'      => '',
                            'items_wrap'      => '<ul id="%1$s">%3$s</ul>',
                            'depth'           => 1,
                            'walker'          => new topbar_walker ()
                        );
                        wp_nav_menu($topbarinfo); ?>
                    </div>
                    <?php get_search_form(); ?>
                </div>
