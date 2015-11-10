<?php
/**
 * The template for displaying pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages and that
 * other "pages" on your WordPress site will use a different template.
 *
 * @package WordPress
 * @subpackage FoundationPress
 * @since FoundationPress 1.0
 */
get_header();  //wp_nav_menu?>

<div class="row" id="main-content">
    <div class="mobile-entry-margin" role="main">
        <?php do_action('foundationpress_before_content'); ?>
        <?php while (have_posts()) : the_post(); ?>
            <article <?php post_class() ?> id="post-<?php the_ID(); ?>">
                <?php do_action('foundationpress_page_before_entry_content'); ?>
              
              
                <div class="entry-content <?php echo is_front_page() ? 'home' : '';?>">
                    <?php //the_content();?>
                </div>
                
                
                
                <?php if(is_front_page()) :?>
                 <div class="news-content" style="visibility: hidden;">
                  <div id="rollArea" style=" position:relative; overflow:hidden;">
                     <ul id="rollPost">
                       <?php echo get_field('input_box');?>
                    </ul>
                  </div>  
                 </div>
                <?php endif; //the_content();?>
                
               
               
                <?php get_template_part('parts/main-menu');?>

            </article>
        <?php endwhile; ?>
        <?php do_action('foundationpress_after_content'); ?>
    </div>
</div>
<?php get_footer(); ?>
