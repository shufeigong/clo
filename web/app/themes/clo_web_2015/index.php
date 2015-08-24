<?php get_header(); ?>
    <div class="row">
        <?php get_template_part('parts/top-bar'); ?>
        <div class="site-header hentry"></div>
        <div class="small-12 large-8 columns" role="main">
            <?php do_action('foundationpress_before_content'); ?>
            <div id="main"></div>
            <?php do_action('foundationpress_after_content'); ?>
        </div>
    </div>
<?php get_footer(); ?>