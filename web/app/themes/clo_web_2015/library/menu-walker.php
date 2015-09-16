<?php
/**
 * Customize the output of menus for Foundation top bar
 *
 * @package WordPress
 * @subpackage FoundationPress
 * @since FoundationPress 1.0.0
 */

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

		// build html
		$output .= $indent . '<li class="' . $depth_class_names . ' ' . $class_names . ' clearfix">';


		// link attributes
		$attributes = !empty($item->attr_title) ? ' title="' . esc_attr($item->attr_title) . '"' : '';
		$attributes .= !empty($item->target) ? ' target="' . esc_attr($item->target) . '"' : '';
		$attributes .= !empty($item->xfn) ? ' rel="' . esc_attr($item->xfn) . '"' : '';
		//$attributes .= !empty($item->url) ? ' href="' . esc_attr($item->url) . '"' : '';
		$attributes .= ' href="#" onclick="return false"';
		$attributes .= ! empty( $item->title)  ? ' id="'   . esc_attr( strtolower(preg_replace("/\s|　/","",$item->title))  ) .'"' : '';
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
		
		if($imgid=='tobehappy'||$imgid=='tolove'||$imgid=='tolearn'||$imgid=='towork'||$imgid=='tolive'||$imgid=='about'||$imgid=='news')
		{
			$output .= '<img src="/app/themes/clo_web_2015/src/img/'.strtolower(str_replace(' ', '', apply_filters('the_title', $item->title, $item->ID))) . '.svg" alt="' . apply_filters('the_title', $item->title, $item->ID) .'"/>';
		}
		else{
			$output .= '<img src="/app/themes/clo_web_2015/src/img/default.svg" alt="' . apply_filters('the_title', $item->title, $item->ID) .'"/>';
		}
		//$output .= '<img src="/app/themes/clo_web_2015/src/img/'.strtolower(str_replace(' ', '', apply_filters('the_title', $item->title, $item->ID))) . '.svg" alt="' . apply_filters('the_title', $item->title, $item->ID) .'"/>';
        
		$output .= '</div>';
        $output.='<hr class="blueline"/>';
		$output.='<div class="contentdiv"></div>';
		$output.='<div class="menudiv"><ul></ul></div>';
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

		// build html
		$output .= $indent . '<li>';


		// link attributes
		$attributes = !empty($item->attr_title) ? ' title="' . esc_attr($item->attr_title) . '"' : '';
		$attributes .= !empty($item->target) ? ' target="' . esc_attr($item->target) . '"' : '';
		$attributes .= !empty($item->xfn) ? ' rel="' . esc_attr($item->xfn) . '"' : '';
		//$attributes .= !empty($item->url) ? ' href="' . esc_attr($item->url) . '"' : '';
		$attributes .= ' href="#" onclick="return false"';
		$attributes .= ! empty( $item->title)  ? ' id="'   . esc_attr( strtolower(preg_replace("/\s|　/","",$item->title))  ) .'M"' : '';
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