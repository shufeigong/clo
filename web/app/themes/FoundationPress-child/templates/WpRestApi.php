<?php
/*
Template Name: WpRestApi
*/

get_header(); ?>
<script type="text/javascript" src="<?php echo get_stylesheet_directory_uri(); ?>/js/jquery.js"></script>
<script type="text/javascript" src="<?php echo get_stylesheet_directory_uri(); ?>/js/WpRestApi.js"></script>

<div class="row">
	<div class="small-12 large-8 columns" role="main">

	<?php do_action( 'foundationpress_before_content' ); ?>

	<?php while ( have_posts() ) : the_post(); ?>
		<article <?php post_class() ?> id="post-<?php the_ID(); ?>">
			<header>
				<h1 class="entry-title"><?php the_title(); ?></h1>
			</header>
			<?php do_action( 'foundationpress_page_before_entry_content' ); ?>
			<div class="entry-content">
				<?php the_content(); ?>
				<table align="center">
					<tr>
					<td>Grab Home Page:</td>
					<td><button id="home">Home</button></td>
					</tr>
					
					<tr>
					<td>Grab Camps Page:</td>
					<td><button id="camps">Camps</button></td>
					</tr>
					
					<tr>
					<td>Grab Riding Page:</td>
					<td><button id="ridingandparties">Riding</button></td>
					</tr>
					
					<tr>
					<td>Grab Club Page:</td>
					<td><button id="clubsandlessons">Club</button></td>
					</tr>
					
					<tr>
					<td>Grab Registration Page:</td>
					<td><button id="registration">Registration</button></td>
					</tr>
				</table>
				
				<div id="grabResult"></div>	
			</div>
			<footer>
				<?php wp_link_pages( array('before' => '<nav id="page-nav"><p>' . __( 'Pages:', 'foundationpress' ), 'after' => '</p></nav>' ) ); ?>
				<p><?php the_tags(); ?></p>
			</footer>
			<?php do_action( 'foundationpress_page_before_comments' ); ?>
			<?php comments_template(); ?>
			<?php do_action( 'foundationpress_page_after_comments' ); ?>
		</article>
	<?php endwhile;?>

	<?php do_action( 'foundationpress_after_content' ); ?>

	</div>
	<?php get_sidebar(); ?>
</div>
<?php get_footer(); ?>
