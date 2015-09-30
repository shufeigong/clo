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

<div class="row" >
    <div class="" role="main">
        <?php do_action('foundationpress_before_content'); ?>

        <?php while (have_posts()) : the_post(); ?>
            <article <?php post_class() ?> id="post-<?php the_ID(); ?>">
                <?php do_action('foundationpress_page_before_entry_content'); ?>
                 
                 <div class="mbl-img">
                 <img src="<?php echo get_stylesheet_directory_uri(); ?>/src/img/mbl-img.svg"/>
                 </div>
                 
                
                 <div class="mbx-dh">
                 <?php  
                        $menu_breadcrumb = new Menu_Breadcrumb( 'main-menu' );   // 'main' is the Menu Location
                        
                        $current_menu_item_object = $menu_breadcrumb->get_current_menu_item_object();
                        if($current_menu_item_object){
                        	
                        	$breadcrumb_array = $menu_breadcrumb->generate_trail();
                            $breadcrumb_markup = $menu_breadcrumb->generate_markup( $breadcrumb_array, ' > ' );
                            echo '<p class="menu-breadcrumb"><a id="gohome">HOME</a> > ' . $breadcrumb_markup . '</p>';
                        }
                        	
                        else
                        {
                        	
                        	echo '<a id="gohome">HOME</a>';
                        }
                  ?>     
                 </div>
                
                
                
                <div class="entry-content <?php echo is_front_page() ? 'home' : '';?>">
                    <?php the_content(); ?>
                </div>
                
                
                                  
                <?php get_template_part('parts/main-menu'); ?>
               
              
               
                <?php if(is_front_page()) :?>
                 <div class="news-content">
                     <ul>
                       <?php echo get_field('input_box');?>
                    </ul>
                 </div>
                <?php endif; ?>
            </article>
        <?php endwhile; ?>

        <?php do_action('foundationpress_after_content'); ?>

    </div>
    <?php //get_sidebar(); ?>    
  
</div>
<?php get_footer(); ?>
