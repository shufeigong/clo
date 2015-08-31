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
    <div class="medium-12" role="main">
        <?php do_action('foundationpress_before_content'); ?>

        <?php while (have_posts()) : the_post(); ?>
            <article <?php post_class() ?> id="post-<?php the_ID(); ?>">
                <?php do_action('foundationpress_page_before_entry_content'); ?>
                <div class="entry-content <?php echo is_front_page() ? 'home' : '';?>">
                    <?php the_content(); ?>
                </div>
                <?php
                $defaults = array(
                    'theme_location'  => 'head-menu',
                    'menu'            => 'topnav',
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
                    'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                    'depth'           => 1,
                    'walker'          => new description_walker()
                );
                wp_nav_menu($defaults);
                ?>
                <?php if(is_front_page()) :?>
                <div class="news-content">

                    <div class="news1 news-item has-video">
                        <div class="arrow"></div>
                        <div class="content-box">
                            <h5>CEO MESSAGE</h5>

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
                        <h5>BE A SUPPORTER</h5>

                        <p>
                            Consectetur adipiscing elit. Donec tincidunt eu tellus nec volutpat. Integer tempus tempor
                            leo ullamcorper accumsan.
                        </p>
                    </div>
                </div>
                <?php endif; ?>
                <footer>
                    <?php wp_link_pages(
                        array(
                            'before' => '<nav id="page-nav"><p>' . __('Pages:', 'foundationpress'),
                            'after'  => '</p></nav>'
                        )
                    ); ?>
                    <p><?php the_tags(); ?></p>
                </footer>
            </article>
        <?php endwhile; ?>

        <?php do_action('foundationpress_after_content'); ?>

    </div>
    <?php //get_sidebar(); ?>
</div>
<?php get_footer(); ?>
