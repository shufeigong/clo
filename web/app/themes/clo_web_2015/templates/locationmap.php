<?php
/*
Template Name: locationmap
*/
get_header('locationmap');  //wp_nav_menu?>

<div class="row" id="main-content">
    <div class="mobile-entry-margin" role="main">
        <?php do_action('foundationpress_before_content'); ?>
        <?php while (have_posts()) : the_post(); ?>
            <article <?php post_class() ?> id="post-<?php the_ID(); ?>">
                <?php do_action('foundationpress_page_before_entry_content'); ?>
                
                <div class="entry-content2">
                    <?php the_content();?>          
                </div>
                
            </article>
        <?php endwhile; ?>
        <?php do_action('foundationpress_after_content'); ?>
    </div>
</div>
<?php get_footer(); ?>
