<?php
/**
 * The template for displaying search form
 *
 * @package WordPress
 * @subpackage FoundationPress
 * @since FoundationPress 1.0
 */

do_action( 'foundationpress_before_searchform' ); ?>
    <form role="search" method="get" id="searchform" action="<?php echo home_url('/'); ?>">

        <?php do_action('foundationpress_searchform_top'); ?>

        <input id="searchtext" type="text" value="" name="s"
               placeholder="<?php esc_attr_e('Keyword Search', 'foundationpress'); ?>">

        <?php do_action('foundationpress_searchform_before_search_button'); ?>

        <input id="searchbutton" type="image" alt="Search"
               src="<?php echo get_stylesheet_directory_uri(); ?>/dist/img/search_icon.svg" />

        <?php do_action('foundationpress_searchform_after_search_button'); ?>
    </form>
<?php do_action( 'foundationpress_after_searchform' ); ?>