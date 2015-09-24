<?php
/**
 * Customize the output of menus for Foundation top bar
 *
 * @package WordPress
 * @subpackage FoundationPress
 * @since FoundationPress 1.0.0
 */
session_start();
$_SESSION['order']=1;

if (!class_exists('Foundationpress_Top_Bar_Walker')) :

    class Foundationpress_Top_Bar_Walker extends Walker_Nav_Menu
    {
        protected $ancestorClassesAtDepths = array();

        function display_element($element, &$children_elements, $max_depth, $depth = 0, $args, &$output)
        {
            $element->has_children = !empty($children_elements[$element->ID]);
            $element->classes[]    = ($element->current || $element->current_item_ancestor) ? 'active' : '';
            $element->classes[]    = ($element->has_children && 1 !== $max_depth) ? 'has-dropdown' : '';

            parent::display_element($element, $children_elements, $max_depth, $depth, $args, $output);
        }

        /**
         * Add main/sub classes to li's and links.
         *
         * @param array|\StdClass $args
         *
         * {@inheritDoc}
         */
        public function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0)
        {
            $indent = ($depth > 0 ? str_repeat("\t", $depth) : ''); // code indent

            // depth dependent classes
            $depth_classes = array(
                'menu-item-depth-' . $depth
            );
            $depth_class_names = esc_attr(implode(' ', $depth_classes));

            // passed classes
            $classes = empty($item->classes) ? array() : (array)$item->classes;
            // Ensure we don't add parent/ancestor styles to an item if they'e already been added to another item.
            // This applies only to parent / ancestor items.
            if (in_array('current-menu-parent', $classes) || in_array('current-menu-ancestor', $classes)) {
                if (isset($this->ancestorClassesAtDepths[$depth])) {
                    $key = array_search('current-menu-parent', $classes);
                    unset($classes[$key]);
                    $key = array_search('current-menu-ancestor', $classes);
                    unset($classes[$key]);
                }

                $this->ancestorClassesAtDepths[$depth] = true;
            }

            $class_names = esc_attr(implode(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item)));

            // build html
            $output .= $indent . '<li class="' . $depth_class_names . ' ' . $class_names . ' clearfix">';


            // link attributes
            $attributes = !empty($item->attr_title) ? ' title="' . esc_attr($item->attr_title) . '"' : '';
            $attributes .= !empty($item->target) ? ' target="' . esc_attr($item->target) . '"' : '';
            $attributes .= !empty($item->xfn) ? ' rel="' . esc_attr($item->xfn) . '"' : '';
            $attributes .= !empty($item->url) ? ' href="' . esc_attr($item->url) . '"' : '';
            $attributes .= ' class="menu-link ' . ($depth > 0 ? 'sub-menu-link' : 'main-menu-link') . '"';

            $item_output = sprintf(
                '%1$s<a%2$s>%3$s%4$s%5$s</a>%6$s',
                $args->before,
                $attributes,
                $args->link_before,
                apply_filters('the_title', $item->title, $item->ID),
                $args->link_after,
                $args->after
            );

            // build html
            $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);

            $output .= '<a class="image-link '
                . strtolower(str_replace(' ', '-', apply_filters('the_title', $item->title, $item->ID)))
                . '" href="' . $item->url . '"><img src="'
                . get_stylesheet_directory_uri() . '/dist/img/'
                . strtolower(str_replace(' ', '', apply_filters('the_title', $item->title, $item->ID)))
                . '.png"/></a>';
        }

        function start_lvl(&$output, $depth = 0, $args = array())
        {
            $output .= "\n<ul class=\"sub-menu dropdown\">\n";
        }

    }


class Main_Nav_walker extends Walker_Nav_Menu
{
    
	function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0)
	{
	   //$order=1;
		$indent = ($depth > 0 ? str_repeat("\t", $depth) : ''); // code indent

		// depth dependent classes
		$depth_classes     = array(
				'menu-item-depth-' . $depth
		);
		$depth_class_names = esc_attr(implode(' ', $depth_classes));

		// passed classes
		$classes = empty($item->classes) ? array() : (array)$item->classes;
		// Ensure we don't add parent/ancestor styles to an item if they'e already been added to another item.
		// This applies only to parent / ancestor items.
		if (in_array('current-menu-parent', $classes) || in_array('current-menu-ancestor', $classes)) {
			if (isset($this->ancestorClassesAtDepths[$depth])) {
				$key = array_search('current-menu-parent', $classes);
				unset($classes[$key]);
				$key = array_search('current-menu-ancestor', $classes);
				unset($classes[$key]);
			}

			$this->ancestorClassesAtDepths[$depth] = true;
		}

		$class_names = esc_attr(implode(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item)));
 		//echo '<pre>';
 		//var_dump( $item );
 		//echo '</pre>';
		// build html
		$output .= $indent . '<li class="' . $depth_class_names . ' ' . $class_names . ' clearfix">';
		
    
		// link attributes
		$attributes = !empty($item->attr_title) ? ' title="' . esc_attr($item->attr_title) . '"' : '';
		$attributes .= !empty($item->target) ? ' target="' . esc_attr($item->target) . '"' : '';
		$attributes .= !empty($item->xfn) ? ' rel="' . esc_attr($item->xfn) . '"' : '';
		$attributes .= !empty($item->url) ? ' href="' . esc_attr($item->url) . '"' : '';
		$attributes .= 'onclick="return false"';
		//htmlentities($text, ENT_COMPAT, 'UTF-8');
		$unwanted_array = array(    'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
				'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
				'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
				'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
				'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );
		
		
		//$attributes .= ! empty( $item->title)  ? ' id="'   . strtr(esc_attr( strtolower(preg_replace("/\s|　/","",$item->title))  ), $unwanted_array) .'"' : '';
		$attributes.='id="'.esc_attr(explode('?',explode('/',$item->url)[3])[0]).'"';
		
		
		$attributes .='order="'.$_SESSION['order'].'"';
		//$attributes .= ! empty( $item->title)  ? ' ui-sref="'   . esc_attr( strtolower(preg_replace("/\s|　/","",$item->title))  ) .'"' : '';
		//$attributes .= ! empty( $item->title)  ? ' id="'   . esc_attr( explode('/', $item->url)[count(explode('/',$item->url))-2]  ) .'"' : '';
		//$imgid=! empty( $item->attr_title)  ? ' id="'   . esc_attr( $item->attr_title   ) .'"' : '';
		$attributes .= ' class="menu-link ' . ($depth > 0 ? 'sub-menu-link' : 'main-menu-link') . '"';

		$item_output = sprintf(
				'%1$s<a%2$s>%3$s%4$s%5$s</a>%6$s',
				$args->before,
				$attributes,
				$args->link_before,
				apply_filters('the_title', strtoupper($item->title), strtoupper($item->ID)),
				$args->link_after,
				$args->after
		);

		// build html
		$output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);

		$output .= '<div class="image-link ' . strtolower(str_replace(' ', '-', apply_filters('the_title', $item->title, $item->ID))) . '">';
		
		$imgid=strtolower(str_replace(' ', '', apply_filters('the_title', $item->title, $item->ID)));
		
		if($_SESSION['order']<8)
		{
			$output .= '<img src="/app/themes/clo_web_2015/src/img/img'.$_SESSION['order'] . '.svg" alt="img' . $_SESSION['order'] .'"/>';
		}
		else{
			$output .= '<img src="/app/themes/clo_web_2015/src/img/default.svg" alt="' . apply_filters('the_title', $item->title, $item->ID) .'"/>';
		}
		//$output .= '<img src="/app/themes/clo_web_2015/src/img/'.strtolower(str_replace(' ', '', apply_filters('the_title', $item->title, $item->ID))) . '.svg" alt="' . apply_filters('the_title', $item->title, $item->ID) .'"/>';
        
		$output .= '</div>';
        $output.='<hr class="blueline"/>';
		$output.='<div class="contentdiv"></div>';
		$output.='<div class="menudiv"><ul></ul></div>';
		$_SESSION['order']++;
	}
}

class MainMobile_Nav_walker extends Walker_Nav_Menu
{

	function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0)
	{
		$indent = ($depth > 0 ? str_repeat("\t", $depth) : ''); // code indent

		// depth dependent classes
		$depth_classes     = array(
				'menu-item-depth-' . $depth
		);
		$depth_class_names = esc_attr(implode(' ', $depth_classes));

		// passed classes
		$classes = empty($item->classes) ? array() : (array)$item->classes;
		// Ensure we don't add parent/ancestor styles to an item if they'e already been added to another item.
		// This applies only to parent / ancestor items.
		if (in_array('current-menu-parent', $classes) || in_array('current-menu-ancestor', $classes)) {
			if (isset($this->ancestorClassesAtDepths[$depth])) {
				$key = array_search('current-menu-parent', $classes);
				unset($classes[$key]);
				$key = array_search('current-menu-ancestor', $classes);
				unset($classes[$key]);
			}

			$this->ancestorClassesAtDepths[$depth] = true;
		}

		$class_names = esc_attr(implode(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item)));

		$children = get_posts(array('post_type' => 'nav_menu_item', 'nopaging' => true, 'numberposts' => 1, 'meta_key' => '_menu_item_menu_item_parent', 'meta_value' => $item->ID));
		
		// build html
		if(!empty($children)){
			$output .= $indent . '<li class="' . $depth_class_names . ' ' . $class_names . ' clearfix">';
		}
		else{
			$output .= $indent . '<li class="' . $depth_class_names . ' ' . $class_names . ' clearfix no-children">';
		}
		
		


		// link attributes
		$attributes = !empty($item->attr_title) ? ' title="' . esc_attr($item->attr_title) . '"' : '';
		$attributes .= !empty($item->target) ? ' target="' . esc_attr($item->target) . '"' : '';
		$attributes .= !empty($item->xfn) ? ' rel="' . esc_attr($item->xfn) . '"' : '';
		$attributes .= !empty($item->url) ? ' href="' . esc_attr($item->url) . '"' : '';
		//$attributes .= ' href="#" onclick="return false"';
		$attributes .= ! empty( $item->title)  ? ' id="'   . esc_attr( strtolower(preg_replace("/\s|　/","",$item->title))  ) .'M"' : '';
		//$attributes .= ! empty( $item->title)  ? ' id="'   . esc_attr( explode('/', $item->url)[count(explode('/',$item->url))-2]  ) .'"' : '';
		//$imgid=! empty( $item->attr_title)  ? ' id="'   . esc_attr( $item->attr_title   ) .'"' : '';
		$attributes .= ' class="menu-link ' . ($depth > 0 ? 'sub-menu-link' : 'main-menu-link') . '"';
		//global $wpdb;
		//$query = $wpdb->prepare("SELECT COUNT(*) FROM $wpdb->posts WHERE post_status ='publish' AND post_type='nav_menu_item' AND post_parent=%d", $item->ID);
		//$child_count = $wpdb->get_var($query);
		
		
		
		
		if(!empty($children)){
		$item_output = sprintf(
				'<a class="mobileSign">[+]</a>'."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".'%1$s<a%2$s>%3$s%4$s%5$s</a>%6$s',
				$args->before,
				$attributes,
				$args->link_before,
				apply_filters('the_title', strtoupper($item->title), strtoupper($item->ID)),
				$args->link_after,
				$args->after
		);
		}
		else{
			$item_output = sprintf(
					'%1$s<a%2$s>%3$s%4$s%5$s</a>%6$s',
					$args->before,
					$attributes,
					$args->link_before,
					apply_filters('the_title', strtoupper($item->title), strtoupper($item->ID)),
					$args->link_after,
					$args->after
			);
		}
		// build html
		$output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
	
	}
}

class Utility_Nav_walker extends Walker_Nav_Menu
{
	function start_el(  &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		global $wp_query;
		$indent = ( $depth > 0 ? str_repeat( "\t", $depth ) : '' ); // code indent

		$depth_class_names='topbarClass';
		// passed classes
		//$classes = empty( $item->classes ) ? array() : (array) $item->classes;
		//$class_names = esc_attr( implode( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item ) ) );

		// build html
		$output .= $indent . '<li id="nav-menu-item-'. $item->ID . '">';

		// link attributes
		$attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
		$attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
		$attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
		$attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';
		//$attributes .= ' class="menu-link ' . ( $depth > 0 ? 'sub-menu-link' : 'main-menu-link' ) . '"';

		$item_output = sprintf( '%1$s<a%2$s>%3$s%4$s%5$s</a>%6$s',
				$args->before,
				$attributes,
				$args->link_before,
				apply_filters( 'the_title',  strtoupper($item->title), strtoupper($item->ID) ),
				$args->link_after,
				$args->after
		);

		// build html
		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	}
}

class UtilityMobile_Nav_walker extends Walker_Nav_Menu
{
	function start_el(  &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		global $wp_query;
		$indent = ( $depth > 0 ? str_repeat( "\t", $depth ) : '' ); // code indent

		$depth_class_names='topbarClass';
		// passed classes
		//$classes = empty( $item->classes ) ? array() : (array) $item->classes;
		//$class_names = esc_attr( implode( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item ) ) );

		// build html
		$output .= $indent . '<li>';

		// link attributes
		$attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
		$attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
		$attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
		$attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';
		//$attributes .= ' class="menu-link ' . ( $depth > 0 ? 'sub-menu-link' : 'main-menu-link' ) . '"';

		$item_output = sprintf( '%1$s<a%2$s>%3$s%4$s%5$s</a>%6$s',
				$args->before,
				$attributes,
				$args->link_before,
				apply_filters( 'the_title',  strtoupper($item->title), strtoupper($item->ID) ),
				$args->link_after,
				$args->after
		);

		// build html
		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	}
}






endif;
?>