<?php
/*
Template Name: locationmap
*/
get_header('locationmap');  //wp_nav_menu?>

<div class="row" id="main-content">
    <div class="mobile-entry" role="main">
        <?php do_action('foundationpress_before_content'); ?>
        <?php while (have_posts()) : the_post(); ?>
            <article2 <?php post_class() ?> id="post-<?php the_ID(); ?>">
                <?php do_action('foundationpress_page_before_entry_content'); ?>
                <?php if(ICL_LANGUAGE_CODE=='en'): ?>
                <div class="entry-content2 map-en">
                    <?php the_content();?>          
                </div>
                <?php elseif(ICL_LANGUAGE_CODE=='fr'): ?>
                <div class="entry-content2 map-fr">
                    <?php the_content();?>          
                </div>
                
                <?php endif;?>   
            </article2>
        <?php endwhile; ?>
        <?php do_action('foundationpress_after_content'); ?>
    </div>
</div>
<?php get_footer(); ?>
