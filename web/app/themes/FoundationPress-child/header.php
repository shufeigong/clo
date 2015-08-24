<!doctype html>
<html class="no-js" <?php language_attributes(); ?>>
  <head>
    <meta charset="utf-8" />
    <meta name="fragment" content="!">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    
    <title><?php wp_title('|', true, 'right'); bloginfo('name'); ?></title>
    <link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/css/app.css" />
    <link rel="shortcut icon" type="image/png" href="<?php echo get_stylesheet_directory_uri(); ?>/favicon.png">
    <script type="text/javascript" src="<?php echo get_stylesheet_directory_uri(); ?>/js/jquery.js"></script>
    <script type="text/javascript" src="<?php echo get_stylesheet_directory_uri(); ?>/js/menu.js"></script>
   
    <?php wp_head(); ?>
  </head>
  <body <?php body_class(); ?>>

  <div class="off-canvas-wrap" data-offcanvas>
  <div class="inner-wrap">

  <nav class="tab-bar show-for-small-only">
    <section class="left-small">
      <a class="left-off-canvas-toggle menu-icon" ><span></span></a>
    </section>
    <section class="middle tab-bar-section">
      
      <h1 class="title"><?php bloginfo( 'name' ); ?></h1>

    </section>
  </nav>

  <aside class="left-off-canvas-menu">
    <?php foundationPress_mobile_off_canvas(); ?>
  </aside>
<header class="row" role="banner">
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
//  		'walker'          => new topbar_walker ()
  );
  wp_nav_menu( $topbarinfo );
   ?>
  <div class="small-12 columns">
    <?php get_search_form();?>
    <h1><a href="<?php bloginfo('url'); ?>" title="<?php bloginfo('name'); ?>"><img alt="logo" src="<?php echo get_stylesheet_directory_uri(); ?>/images/clo_logo.png"/></a></h1>
  </div>
</header>

<section class="container" role="document">