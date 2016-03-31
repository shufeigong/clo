<?php
if (! class_exists('SLPTagalong_AdminPanel')) {

    /**
     * Manage admin panel interface elements for Tagalong.
     *
     * @package StoreLocatorPlus\Tagalong\AdminPanel
     * @author Lance Cleveland <lance@charlestonsw.com>
     * @copyright 2012-2013 Charleston Software Associates, LLC
     */
    class SLPTagalong_AdminPanel {


        //-------------------------------------
        // Properties
        //-------------------------------------

        /**
         * This addon pack.
         *
         * @var \SLPTagalong $addon
         */
        private $addon;

        /**
         * The csvImporter object.
         *
         * @var \CSVImportCategories $csvImporter
         */
        public $csvImporter;

        /**
         * The Tagalong Admin object.
         *
         * @var \SLPTagalong_Admin $parent
         */
        private $parent = null;

        /**
         * Connect the list walker object here.
         *
         * @var \Tagalong_CategoryWalker_List
         */
        public $ListWalker;

        /**
         * The base SLPlus object.
         *
         * @var \SLPlus $slplus
         */
        private $slplus;


        //-------------------------------------
        // Methods
        //-------------------------------------

        /**
         * Instantiate the admin panel object.
         * 
         * @param SLPTagalong $parent
         */
        function __construct($params) {
           // Set properties based on constructor params,
            // if the property named in the params array is well defined.
            //
            if ($params !== null) {
                foreach ($params as $property=>$value) {
                    if (property_exists($this,$property)) { $this->$property = $value; }
                }
            }
        }

        /**
         * Render the admin panel.
         */
        function renderPage() {
            // Display any notices
            $this->slplus->notifications->display();

            //-------------------------
            // Navbar Section
            //-------------------------
            $this->parent->settings->add_section(
                array(
                    'name'          => 'Navigation',
                    'div_id'        => 'navbar_wrapper',
                    'description'   => $this->slplus->AdminUI->create_Navbar(),
                    'innerdiv'      => false,
                    'is_topmenu'    => true,
                    'auto'          => false,
                    'headerbar'     => false
                )
            );

            //-------------------------
            // Tagalong Settings Panel
            //-------------------------
            $panelName  = __('Tagalong Settings','csa-slp-tagalong');
            $this->parent->settings->add_section(array('name' => $panelName));

            // Group : Search
            $groupName  = __('Search'         ,'csa-slp-tagalong');
            $this->parent->settings->add_ItemToGroup(array(
                'section'       => $panelName                                           ,
                'group'         => $groupName                                           ,
                'type'          => 'dropdown'                                             ,
                'label'         => __('Show Categories On Search','csa-slp-tagalong')   ,
                'setting'       => 'TAGALONG-show_cats_on_search'                       ,
                'custom'        =>
                    array(
                        array(
                            'label'     => __('No','csa-slp-tagalong'),
                            'value'     =>'',
                            'selected'  => ($this->addon->options['show_cats_on_search'] === ''),
                            ),
                        array(
                            'label'     => __('Single','csa-slp-tagalong'),
                            'value'     => 'single',
                            'selected'  => ($this->addon->options['show_cats_on_search'] === 'single'),
                            ),
                        array(
                            'label'     => __('Cascading','csa-slp-tagalong'),
                            'value'     => 'cascade',
                            'selected'  => ($this->addon->options['show_cats_on_search'] === 'cascade'),
                            ),
                    ),
                'description'   =>
                    __('How to show the category selector on the search form.','csa-slp-tagalong') . ' ' .
                    __('No will not show the category selector.','csa-slp-tagalong') . ' ' .
                    __('Single shows the category selector as a single drop down with indents if there are parent/children relations.','csa-slp-tagalong') . ' ' .
                    __('Cascading shows a single drop down showing parents then children drop downs on an as-needed basis.','csa-slp-tagalong')
                ));
            if ($this->addon->StorePagesActive) {
                $this->parent->settings->add_ItemToGroup(array(
                    'section'       => $panelName                                                       ,
                    'group'         => $groupName                                                       ,
                    'type'          => 'slider'                                                         ,
                    'setting'       => 'TAGALONG-hide_empty'                                            ,
                    'value'          => $this->slplus->is_CheckTrue($this->addon->options['hide_empty']),
                    'label'         => __('Hide Unpublished Categories','csa-slp-tagalong')             ,
                    'description'   =>
                        __('Only works if Store Pages is installed.  Hide the empty categories from the category selector. Store Pages in draft mode are considered "empty".','csa-slp-tagalong'),
                    ));
            }
            $this->parent->settings->add_ItemToGroup(array(
                'section'       => $panelName                                   ,
                'group'         => $groupName                                   ,
                'type'          => 'text'                                       ,
                'setting'       => 'TAGALONG-show_option_all'                   ,
                'value'         =>
                    $this->slplus->WPML->getWPMLText(
                        'TAGALONG-show_option_all'          ,
                        $this->addon->options['show_option_all']   ,
                        'csa-slp-tagalong'
                    ),
                'label'         => __('Any Category Label','csa-slp-tagalong')  ,
                'description'   =>
                    __('If set, prepends this text to select "any category" as an option to the selector. Set to blank to not provide the any selection.','csa-slp-tagalong'),
                    ));
            $this->parent->settings->add_ItemToGroup(array(
                'section'       => $panelName                                       ,
                'group'         => $groupName                                       ,
                'type'          => 'text'                                           ,
                'setting'       => 'TAGALONG-label_category'                        ,
                'value'         =>
                    $this->slplus->WPML->getWPMLText(
                        'TAGALONG-label_category'          ,
                        $this->addon->options['label_category']   ,
                        'csa-slp-tagalong'
                    ),
                'label'         => __('Category Select Label','csa-slp-tagalong')   ,
                'description'   =>
                    __('The label for the category selector.','csa-slp-tagalong'),
                 ));

            // Group : Map
            //
            $groupName  = __('Map'         ,'csa-slp-tagalong');
            $this->parent->settings->add_ItemToGroup(array(
                    'section'       => $panelName                                   ,
                    'group'         => $groupName                                   ,
                    'type'          => 'slider'                                     ,
                    'setting'       => 'TAGALONG-default_icons'                     ,
                    'value'         => $this->addon->options['default_icons']      ,
                    'label'         => __('Use Default Markers','csa-slp-tagalong') ,
                    'description'   =>
                        __('Do not use custom tagalong destination markers on map, use default Map Settings markers.','csa-slp-tagalong')
                    ));

            // Group : Results
            //
            $groupName  = __('Results'         ,'csa-slp-tagalong');
            $this->parent->settings->add_ItemToGroup(array(
                    'section'       => $panelName                                   ,
                    'group'         => $groupName                                   ,
                    'type'          => 'slider'                                     ,
                    'setting'       => 'TAGALONG-show_icon_array'                   ,
                    'value'         => $this->addon->options['show_icon_array']    ,
                    'label'         => __('Show Icon Array','csa-slp-tagalong') ,
                    'description'   =>
                        __('When enabled an array of icons will be created in the below map results and info bubble.',
                           'csa-slp-tagalong'
                          )
                    ));
            $this->parent->settings->add_ItemToGroup(array(
                    'section'       => $panelName                                       ,
                    'group'         => $groupName                                       ,
                    'type'          => 'slider'                                         ,
                    'setting'       => 'TAGALONG-ajax_orderby_catcount'                 ,
                    'value'         => $this->addon->options['ajax_orderby_catcount']  ,
                    'label'         => __('Order By Category Count','csa-slp-tagalong') ,
                    'description'   =>
                        __('When enabled the results will be ordered by those with the most categories assigned appearing first.',
                           'csa-slp-tagalong'
                          )
                    ));

            // Group : View
            //
            $this->slplus->createobject_AddOnManager();
            $groupName  = __('View'         ,'csa-slp-tagalong');
            $this->parent->settings->add_ItemToGroup(array(
                    'section'       => $panelName                                   ,
                    'group'         => $groupName                                   ,
                    'type'          => 'slider'                                     ,
                    'setting'       => 'TAGALONG-show_legend_text'                  ,
                    'value'         => $this->addon->options['show_legend_text']    ,
                    'label'         => __('Show Text Under Legend','csa-slp-tagalong') ,
                    'description'   =>
                        __('When enabled text will appear under each Tagalong icon in the legend. ','csa-slp-tagalong') .
                        sprintf(__('Add legend to the output with the [tagalong legend] shortcode via the %s view setting.','csa-slp-tagalong'),
                                $this->slplus->add_ons->available['slp-pro']['link'])
                    )
             );


            //-------------------------
            // Store Categories Panel
            //-------------------------
            $panelName  = __('Store Categories','csa-slp-tagalong');
            $this->parent->settings->add_section(array('name' => $panelName));

            // Group : Manage
            //
            $groupName  = __('Manage'         ,'csa-slp-tagalong');
            $this->parent->settings->add_ItemToGroup(array(
                    'section'       => $panelName   ,
                    'group'         => $groupName   ,
                    'type'          => 'subheader'  ,
                    'label'         => ''           ,
                    'show_label'    => false        ,
                    'description'   =>
                        sprintf(
                            __('The <a href="%s">Category Manager</a>','csa-slp-tagalong'),
                            admin_url() . 'edit-tags.php?taxonomy=stores'
                            ) .
                        __(' provides a full management interface for Store Categories.', 'csa-slp-tagalong')
                    ));

            // Import Form
            //
            $this->create_CSVCategoryImporter(array('section_name'=>$panelName, 'group_name'=>$groupName));
            $this->csvImporter->create_BulkUploadForm();

            // Group : Category List
            //
            $groupName  = __('List','csa-slp-tagalong');

            // Create the output for the category list...
            //
            $description =
                __('This category list is based on the custom stores taxonomy setup by Store Locator Plus.', 'csa-slp-tagalong') .
                ' ' .
                __('It is the same taxonomy used by the Store Pages add-on.', 'csa-slp-tagalong') .
                $this->createstring_StoreCategoryList();

            $description .= '<p class="footernote">';
            if ($this->addon->StorePagesActive) {
                $description .=
                        __('Clicking on a link will bring you to the most recently touched Store Page.', 'csa-slp-tagalong') .
                        ' '.
                        __('Depending on your theme, you may have an older/newer posts button on the bottom of each page to navigate the matching pages.','csa-slp-tagalong')
                        ;
            } else {
                $description .= '<br/> ' .
                    sprintf(__('The links will show a list of stores in that category if <a href="%s" target="CSA">Store Pages</a>, a separate add-on pack, is installed.','csa-slp-tagalong'),
                            'http://www.charlestonsw.com/product-category/slplus/')
                    ;
            }
            $description .= '</p>';

            $this->parent->settings->add_ItemToGroup(array(
                    'section'       => $panelName       ,
                    'group'         => $groupName       ,
                    'type'          => 'subheader'      ,
                    'label'         => ''               ,
                    'show_label'    => false            ,
                    'description'   => $description
                    ));

            //------------------------------------------
            // RENDER
            //------------------------------------------
            $this->parent->settings->render_settings_page();
        }

        /**
         * Create and attach the \CSVImportLocations object
         */
        function create_CSVCategoryImporter($params=null) {
            if (!class_exists('CSVImportCategories')) {
                require_once(plugin_dir_path(__FILE__).'class.csvimport.categories.php');
            }
            if ($params===null) { $params = array(); }
            if (!isset($this->csvImporter)) {
                $this->csvImporter =
                    new CSVImportCategories(
                        array_merge(
                            array(
                                'parent'    => $this->parent        ,
                                'plugin'    => $this->slplus
                            ),
                            $params
                         )
                    );
            }
        }

        /**
         * Render the list of store categories.
         *
         *  @return string HTML for the category list.
         */
        function createstring_StoreCategoryList() {
            $this->create_CategoryWalkerForList();
            return
                '<ul id="tagalong_category_list">' .
                wp_list_categories(
                        array(
                            'echo'              => '0'                ,
                            'hierarchical'      => '1'                ,
                            'hide_empty'        => '0'                ,
                            'show_count'        => '1'                ,
                            'taxonomy'          => 'stores'         ,
                            'title_li'          => ''               ,
                            'walker'            => $this->ListWalker
                        )
                    ) .
                '</ul>'
                ;
        }

        /**
         * Setup the List category walker object.
         */
        function create_CategoryWalkerForList() {
            if (class_exists('Tagalong_CategoryWalker_List') == false) {
                require_once(plugin_dir_path(__FILE__).'class.categorywalker.list.php');
            }
            if (!isset($this->ListWalker)) {
                $this->ListWalker = new Tagalong_CategoryWalker_List(array('parent'=>$this));
            }
        }

        /**
         * Process an incoming CSV import file.
         */
        function process_CSVCategoryFile() {
            $this->create_CSVCategoryImporter();
            $this->csvImporter->process_File();
        }

    }
}