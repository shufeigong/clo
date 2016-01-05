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
<?php do_action('foundationpress_after_body'); ?>
<?php

$userAgent = parse_user_agent();

if ($userAgent['browser'] == 'MSIE' && (int)$userAgent['version'] < 11) {
    echo '<p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/?locale=en/">upgrade your browser</a> to improve your experience.</p>';

} ?>
<div class="off-canvas-wrap" data-offcanvas>
    <div class="inner-wrap">
       
    

<section class="container" role="document">
<?php do_action('foundationpress_after_header'); ?>
<div class="row">

     <?php if(is_user_logged_in()):?>
    <div class="row header login">
     <?php else:?>
    <div class="row header non-login">
     <?php endif;?>

	        
       
       
<?php //$userAgent = parse_user_agent(); echo $userAgent['browser'].'////'.$userAgent['version'][0];?>
             
    </div>
