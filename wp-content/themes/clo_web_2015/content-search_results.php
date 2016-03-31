<?php
/**
 * The default template for displaying content
 *
 * Used for both single and index/archive/search.
 *
 * @package WordPress
 * @subpackage FoundationPress
 * @since FoundationPress 1.0.0
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header> 
		<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<?php foundationpress_entry_meta(); ?>
	</header>
	<div class="entry-content" style="margin-bottom: 30px;">
		<?php 
		$theContent = strip_tags(get_the_content( __( 'Continue reading...', 'foundationpress' ) ));
		
		if(mb_strlen($theContent)>200){
			$theContent = mb_substr($theContent,0,200,"UTF8")."...";
		}
		echo '<p>'.$theContent.'</p>'; ?>
	</div>
	
	<hr style="border-top:1px solid #0075c9;"/>
</article>