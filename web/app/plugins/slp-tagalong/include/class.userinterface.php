<?php
if (! class_exists('SLPTagalong_UI')) {
	require_once(SLPLUS_PLUGINDIR.'/include/base_class.userinterface.php');

    /**
     * Holds the UI-only code.
     *
     * This allows the main plugin to only include this file in the front end
     * via the wp_enqueue_scripts call.   Reduces the back-end footprint.
     *
     * @package StoreLocatorPlus\Tagalong\UI
     * @author Lance Cleveland <lance@charlestonsw.com>
     * @copyright 2014 Charleston Software Associates, LLC
     */
    class SLPTagalong_UI extends SLP_BaseClass_UI {

        //-------------------------------------
        // Properties
        //-------------------------------------

        /**
         * This addon pack.
         *
         * @var \SLPTagalong $addon
         */
        public $addon;

	    /**
	     * The category drop downs.
	     *
	     * @var mixed[] $categoryDropDowns
	     */
	    private $categoryDropDowns = array();

	    /**
	     * Connect the legend walker object here.
	     *
	     * @var \Tagalong_CategoryWalker_Legend $LegendWalker
	     */
	    private $LegendWalker;

	    /**
	     * Which level of the nested drop down tree are we processing?
	     *
	     * @var int
	     */
	    private $node_level = 0;

        //-------------------------------------
        // Methods : activity
        //-------------------------------------

        /**
         * Add WordPress and SLP hooks and filters.
         */
        public function add_hooks_and_filters() {
	        add_filter   ('slp_layout'                  ,array($this,'filter_AddLegend'              ),95    );
	        add_filter   ('slp_shortcode_atts'          ,array($this,'filter_SetAllowedShortcodes'   )       );
	        add_filter   ('shortcode_slp_searchelement' ,array($this,'filter_ProcessSearchElement'   )       );
	        add_filter   ('shortcode_storepage'         ,array($this,'filter_ProcessStorePage'       )       );
	        add_filter   ('slp_searchlayout'            ,array($this,'filter_ModifySearchLayout'     ),999   );
	        add_shortcode('tagalong'                    ,array($this,'process_TagalongShortcode'     )       );
        }

	    /**
	     * Setup the legend category walker object.
	     */
	    function create_CategoryWalkerForLegend() {
		    if (class_exists('Tagalong_CategoryWalker_Legend') == false) {
			    require_once('class.categorywalker.legend.php');
		    }
		    if ( ! isset ( $this->LegendWalker ) ) {
			    $this->LegendWalker = new Tagalong_CategoryWalker_Legend(array('addon'=>$this->addon,'slplus'=>$this->slplus));
		    }
	    }


	    /**
	     * Create a drop down object for all items with a parent category as specified.
	     *
	     * Recursive, calls the same method for each child.
	     *
	     * @param string $parent_cat the parent category (int)
	     * @param mixed $grandparent_cat the grandparent category (int) or null
	     * @return mixed
	     */
	    function create_DropDownForCat($parent_cat,$grandparent_cat=null) {
		    if (!ctype_digit($parent_cat)) { return array(); }
		    
		    $categories = get_categories(
			    array(
				    'hierarchical'      => false,
				    'hide_empty'        => false,
				    'orderby'           => 'name',
				    'parent'            => $parent_cat,
				    'taxonomy'          => SLPLUS::locationTaxonomy
			    )
		    );
		    
		    if (count($categories)<=0) { return array(); }
		    
		    $dropdownItems = array();
		    $dropdownItems[] =
			    array(
				    'label' => $this->addon->options['show_option_all'],
				    'value' => ''
			    );
		    foreach ($categories as $category) {
			    $dropdownItems[] =
				    array(
					    'label' => $category->name,
					    'value' => $category->term_id
				    );
			    $this->create_DropDownForCat($category->term_id,$parent_cat);
		    }
		    $this->categoryDropDowns[] = array(
			    'grandparent' => $grandparent_cat,
			    'parent'    => $parent_cat,
			    'id'        => 'catsel_'.$parent_cat,
			    'name'      => 'catsel_'.$parent_cat,
			    'items'     => $dropdownItems,
			    'onchange'  =>
				    "jQuery('#children_of_{$parent_cat}').children('div.category_selector.child').hide();" .
				    "childDD='#children_of_'+jQuery('option:selected',this).val();" .
				    "jQuery(childDD).show();" .
				    "jQuery(childDD+' option:selected').prop('selected',false);" .
				    "jQuery(childDD+' option:first').prop('selected','selected');" .
				    "if (jQuery('option:selected',this).val()!=''){jQuery('#cat').val(jQuery('option:selected',this).val());}" .
				    "else{jQuery('#cat').val(jQuery('#catsel_{$grandparent_cat} option:selected').val());}"
		    );
	    }

	    /**
	     * Create a cascading drop down array for location categories.
	     *
	     */
	    function createstring_CascadingCategoryDropDown() {
		    $this->addon->debugMP('msg',__FUNCTION__);


		    // Build the category drop down object array, recursive.
		    //
		    $this->create_DropDownForCat('0');
		    $HTML = '<input type="hidden" id="cat" name="cat" value=""/>';

		    // Create the drop down HTML for each level
		    //
		    if (count($this->categoryDropDowns) > 0) {
			    $this->categoryDropDowns = array_reverse($this->categoryDropDowns);
			    $nested_html = $this->createstring_NestedDropDownDivs($this->categoryDropDowns[0]['parent']);
		    } else {
			    $nested_html = '';
		    }

		    return
			    $HTML .
			    '<div id="tagalong_cascade_dropdowns">' .
			    $nested_html .
			    '</div>'
			    ;
	    }
	    
	    /**
	     * Add our custom category selection div to the search form.
	     *
	     * @return string the HTML for this div appended to the other HTML
	     */
	    function createstring_CategorySelector() {
		    $this->addon->debugMP('msg',__FUNCTION__);

		    // Only With Category shortcode.
		    //
		    if ( ! empty( $this->slplus->data['only_with_category'] ) ) {
			    $category_list = array();
			    $category_slugs = preg_split( '/,/' , $this->slplus->data['only_with_category'] );
			    foreach ( $category_slugs as $slug ) {
				    $category = get_term_by('slug',sanitize_title( $slug ),SLPlus::locationTaxonomy);
				    if ( $category ) { $category_list[] = $category->term_id; }
			    }
			    $category_id_list = join( ',' , $category_list );
			    $this->addon->options['show_cats_on_search'] = 'only_with_category';
		    } else {
			    $category_id_list = '';
		    }

		    // Process the category selector type
		    //
		    switch ($this->addon->options['show_cats_on_search']) {

			    case 'only_with_category':
				    if ( ! empty( $category_id_list ) ) {
					    $HTML =
						    "<input type='hidden' name='cat' id='cat' ".
						    "value='{$category_id_list}' " .
						    "textvalue='{$this->slplus->data['only_with_category']}' " .
						    '/>';
				    } else {
					    $HTML = "<!-- only_with_category term {$this->slplus->data['only_with_category']}  does not exist -->";
				    }
				    break;

			    // Single Style Menu
			    //
			    case 'single':
				    $HTML =
					    '<div id="tagalong_category_selector" class="search_item">' .
					    '<label for="cat">'.
					    $this->slplus->WPML->getWPMLText(
						    'TAGALONG-label_category'           ,
						    $this->addon->options['label_category']    ,
						    'csa-slp-tagalong'
					    ) .
					    '</label>'.
					    wp_dropdown_categories(
						    array(
							    'echo'              => 0,
							    'hierarchical'      => 1,
							    'depth'             => 99,
							    'hide_empty'        => ( $this->slplus->is_CheckTrue( $this->addon->options['hide_empty'] ) ? 1 : 0 ),
							    'orderby'           => 'NAME',
							    'show_option_all'   =>
								    $this->slplus->WPML->getWPMLText(
									    'TAGALONG-show_option_all'          ,
									    $this->addon->options['show_option_all']   ,
									    'csa-slp-tagalong'
								    ),
							    'taxonomy'          => SLPLUS::locationTaxonomy
						    )
					    ).
					    '</div>'
				    ;
				    break;

			    // Cascading Style Menu
			    //
			    case 'cascade':
				    $HTML =
					    '<div id="tagalong_category_selector" class="search_item">' .
					    '<label for="cat">'.
					    $this->slplus->WPML->getWPMLText(
						    'TAGALONG-label_category'           ,
						    $this->addon->options['label_category']    ,
						    'csa-slp-tagalong'
					    )     .
					    '</label>'.
					    $this->createstring_CascadingCategoryDropDown().
					    '</div>'
				    ;
				    break;

			    default:
				    $HTML = '';
				    break;
		    }

		    return $HTML;
	    }
	    
	    /**
	     * Create the LegendHTML String.
	     *
	     * @return string
	     */
	    function createstring_LegendHTML() {
		    $this->addon->debugMP('msg',__FUNCTION__);

		    $this->create_CategoryWalkerForLegend();

		    $HTML =
			    '<div id="tagalong_legend">'                        .
			    '<div id="tagalong_list">'                      .
			    wp_list_categories(
				    array(
					    'echo'              => 0,
					    'hierarchical'      => 0,
					    'depth'             => 99,
					    'hide_empty'        => ( $this->slplus->is_CheckTrue( $this->addon->options['hide_empty'] ) ? 1 : 0 ),
					    'style'             => 'none',
					    'taxonomy'          => 'stores',
					    'walker'            => $this->LegendWalker
				    )
			    ) .
			    '</div>'.
			    '</div>'
		    ;
		    return $HTML;
	    }

	    /**
	     * Create nested divs with the drop down menus within.
	     *
	     * @param string $parent_category_id (int)
	     * @return string
	     */
	    function createstring_NestedDropDownDivs($parent_category_id) {
		    $this->node_level++;
		    $HTML = '';
		    foreach ($this->categoryDropDowns as $dropdown) {
			    if ($dropdown['parent']===$parent_category_id) {
				    $HTML .=
					    "<div id='div_{$dropdown['id']}' name='div_{$dropdown['id']}' class='category_selector parent' >" .
					    $this->slplus->helper->createstring_DropDownMenu(
						    array(
							    'id'        => $dropdown['id'],
							    'name'      => $dropdown['name'],
							    'onchange'  => $dropdown['onchange'],
							    'items'     => $dropdown['items']
						    )
					    ) .
					    '</div>'
				    ;
				    foreach ($dropdown['items'] as $item) {
					    $HTML .= $this->createstring_NestedDropDownDivs($item['value']);
				    }
			    }
		    }

		    if (!empty($HTML)) {
			    $parent_or_child = (($this->node_level === 1) ? 'parent':'child');
			    $HTML =
				    "<div id='children_of_{$parent_category_id}' class='category_selector {$parent_or_child} level_{$this->node_level}'>" .
				    $HTML .
				    '</div>';
		    }

		    $this->node_level--;
		    return $HTML;
	    }

	    /**
	     * Add the Tagalong shortcode processing to whatever filter/hook we need it latched on to.
	     *
	     * The [tagalong] shortcode, used here, is setup in slp_init.
	     */
	    function filter_AddLegend($layoutString) {
		    $this->addon->debugMP('msg',__FUNCTION__,$layoutString);
		    return do_shortcode($layoutString);
	    }

	    /**
	     * Add Tagalong category selector to search layout.
	     *
	     */
	    function filter_ModifySearchLayout($layout) {
		    $this->addon->debugMP('msg',__FUNCTION__);

		    if ( ! empty ( $this->slplus->data['only_with_category'] ) ) { $this->addon->options['show_cats_on_search'] = '1'; }
		    if ( empty( $this->addon->options['show_cats_on_search'] ) ) { return $layout; }

		    if (preg_match('/\[slp_search_element\s+.*dropdown_with_label="category".*\]/i',$layout)) { return $layout; }
		    return $layout . '[slp_search_element dropdown_with_label="category"]';
	    }	    

	    /**
	     * Perform extra search form element processing.
	     *
	     * @param mixed[] $attributes
	     * @return mixed[]
	     */
	    function filter_ProcessSearchElement($attributes) {
		    $this->addon->debugMP('pr',__FUNCTION__,$attributes);

		    foreach ($attributes as $name=>$value) {

			    switch (strtolower($name)) {

				    case 'selector_with_label':
				    case 'dropdown_with_label':
					    switch ($value) {
						    case 'category':
							    return array(
								    'hard_coded_value' =>
									    ! empty( $this->addon->options['show_cats_on_search'] )    ?
										    $this->createstring_CategorySelector()          :
										    ''
							    );
							    break;

						    default:
							    break;
					    }
					    break;

				    default:
					    break;
			    }
		    }

		    return $attributes;
	    }

	    /**
	     * Perform extra search form element processing.
	     *
	     * @param mixed[] $attributes
	     * @return mixed[]
	     */
	    function filter_ProcessStorePage($attributes) {
		    $this->addon->debugMP('pr',__FUNCTION__,$attributes);
		    $this->addon->set_LocationCategories();

		    // No categories set?  Get outta here...
		    //
		    if ( count( $this->addon->current_location_categories ) < 1 ) { return $attributes; }

		    foreach ($attributes as $name=>$value) {

			    switch (strtolower($name)) {

				    case 'field':
					    switch ($value) {
						    case 'iconarray':
							    return array(
								    'hard_coded_value' => $this->addon->createstring_IconArray()
							    );
							    break;

						    default:
							    break;
					    }
					    break;

				    default:
					    break;
			    }
		    }

		    return $attributes;
	    }

	    /**
	     * Set the allowed shortcode attributes
	     *
	     * @param mixed[] $atts
	     * @return mixed[]
	     */
	    function filter_SetAllowedShortcodes($atts) {
		    return array_merge(
			    array(
				    'only_with_category'     => null,
			    ),
			    $atts
		    );
	    }

	    /**
	     * Process the Tagalong shortcode
	     */
	    function process_TagalongShortcode($atts) {
		    $this->addon->debugMP('msg',__FUNCTION__);
		    if (is_array($atts)) {
			    $theKeys = array_map('strtolower',$atts);
			    switch ($theKeys[0]) {
				    case 'legend':
					    return $this->createstring_LegendHTML();
					    break;

				    default:
					    break;
			    }
			    return '';
		    }
	    }	    
    }
}