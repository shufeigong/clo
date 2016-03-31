<?php
/**
 * The template for displaying search results pages.
 *
 * @package WordPress
 * @subpackage FoundationPress
 * @since FoundationPress 1.0
 */
if($_REQUEST['s']==''){header("location:/");}

get_header(); ?>

<div class="row" id="main-content">
	<div class="mobile-entry-margin" role="main">

		<?php do_action( 'foundationpress_before_content' ); ?>
	<div class="entry-content" style="margin-bottom:10px;">
	         <br/>
			<h2><?php _e( 'Search Results for', 'foundationpress' ); ?> "<?php echo get_search_query(); ?>"</h2>
	
		<?php if ( have_posts() ) : ?>
	
			<?php while ( have_posts() ) : the_post(); ?>
				<?php get_template_part( 'content', 'search_results' ); ?>
			<?php endwhile; ?>
	
			<?php else : ?>
				<?php get_template_part( 'content', 'none' ); ?>
	
		<?php endif;?>
	</div>
		<?php do_action( 'foundationpress_before_pagination' ); ?>
	
		<?php if ( function_exists( 'foundationpress_pagination' ) ) { foundationpress_pagination(); } else if ( is_paged() ) { ?>
	
			<nav id="post-nav">
				<div class="post-previous"><?php next_posts_link( __( '&larr; Older posts', 'foundationpress' ) ); ?></div>
				<div class="post-next"><?php previous_posts_link( __( 'Newer posts &rarr;', 'foundationpress' ) ); ?></div>
			</nav>
		<?php } ?>
	
	<?php do_action( 'foundationpress_after_content' ); ?>
     </div>
</div>
	<?php //get_sidebar(); ?>

<?php get_footer(); ?>
