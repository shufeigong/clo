<?php
/**
 * The data interface helper.
 *
 * @package StoreLocatorPlus\Tagalong\Tagalong_CategoryWalker_Legend
 * @copyright 2013 Charleston Software Associates, LLC
 *
 */
class Tagalong_CategoryWalker_Legend extends Walker_Category {

    /**
     * This add-on object.
     *
     * @var \SLPTagalong object $addon
     */
    private $addon;

    /**
     * The slplus base plugin object.
     *
     * @var \SLPlus
     */
    private $slplus;

    /**
     * Create the Walker object.
     *
     * Allows or passing of parent property.
     *
     * @param mixed[] $params
     */
    function __construct($params) {
        foreach ($params as $key=>$val) { $this->$key = $val; }
    }
    
	/**
	 * @see Walker_Category::start_el()
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $category Category data object.
	 * @param int $depth Depth of category in reference to parents.
	 * @param array $args
	 */
	function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
        $output .=
            '<span class="tagalong_legend_icon" id="legend_'.$category->slug.'">' .
                $this->addon->create_LocationIcons(
                        array(
                            $this->create_CategoryDetails($category->term_id),
                        ),
                        array(
                            'show_label' => $this->slplus->is_CheckTrue( $this->addon->options['show_legend_text'] ) ,
                        )
                    ) .
            '</span>'
            ;
	}

    /**
     * Create extended category details array.
     *
     * @param int $category
     * @return mixed[]
     */
    function create_CategoryDetails($category) {
        if (!ctype_digit($category)) { return; }
        $categoryDetails = get_term($category,'stores',ARRAY_A);
        $categoryDetails = array_merge($categoryDetails,$this->addon->get_TermWithTagalongData($category));
        return $categoryDetails;
    }
}
