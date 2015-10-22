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

        <label for="searchtext" class="hidden">Search box</label>
        <input id="searchtext" type="text" value="" name="s"
               placeholder="<?php if(ICL_LANGUAGE_CODE=='en')esc_attr_e('Keyword Search', 'foundationpress');else if(ICL_LANGUAGE_CODE=='fr')esc_attr_e('Recherche par mot clé', 'foundationpress') ?>">

        <?php do_action('foundationpress_searchform_before_search_button'); ?>

        <label for="searchbutton" class="hidden">Search button</label>
        <input id="searchbutton" type="image" alt="Search"
               src="<?php echo get_stylesheet_directory_uri(); ?>/dist/img/search_icon.svg"/>

        <?php do_action('foundationpress_searchform_after_search_button'); ?>
    </form>
<?php do_action( 'foundationpress_after_searchform' ); ?>