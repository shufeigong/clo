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

<div class="row">
    <div class="" role="main">
        <?php do_action('foundationpress_before_content'); ?>

        <?php while (have_posts()) : the_post(); ?>
            <article <?php post_class() ?> id="post-<?php the_ID(); ?>">
                <?php do_action('foundationpress_page_before_entry_content'); ?>
                <div class="entry-content <?php echo is_front_page() ? 'home' : '';?>">
                    <?php the_content(); ?>
                </div>
                <?php get_template_part('parts/main-menu'); ?>

                <?php if(is_front_page()) :?>
                <div class="news-content">
                    <div class="news1 news-item has-video">
                        <div class="arrow"></div>
                        <div class="content-box">
                            <h2>CEO MESSAGE</h2>

                            <p>
                                Consectetur adipiscing elit. Donec tincidunt eu tellus nec volutpat. Integer
                            </p>
                        </div>
                        <div class="video-box">
                            <div class="play-button"><span class="arrow"></span></div>
                        </div>
                        <div class="clearfix"></div>
                    </div>

                    <div class="news2 news-item">
                        <div class="arrow"></div>
                        <h2>BE A SUPPORTER</h2>

                        <p>
                            Consectetur adipiscing elit. Donec tincidunt eu tellus nec volutpat. Integer tempus tempor
                            leo ullamcorper accumsan.
                        </p>
                    </div>
                </div>
                <?php endif; ?>
            </article>
        <?php endwhile; ?>

        <?php do_action('foundationpress_after_content'); ?>

    </div>
    <?php //get_sidebar(); ?>
</div>
<?php get_footer(); ?>
