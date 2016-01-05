<?php
if (!defined( 'ABSPATH'     )) { exit;   } // Exit if accessed directly, dang hackers

// Make sure the class is only defined once.
//
if (!class_exists('CSVImport')) {
    require_once(SLPLUS_PLUGINDIR . '/include/class.csvimport.php');
}

// Make sure the class is only defined once.
//
if (!class_exists('CSVImportCategories')) {

    /**
     * CSV Import of Categories
     *
     * @package StoreLocatorPlus\Tagalong\CSVImportCategories
     * @author Lance Cleveland <lance@charlestonsw.com>
     * @copyright 2013 Charleston Software Associates, LLC
     */
    class CSVImportCategories extends CSVImport {

        //-------------------------
        // Properties
        //-------------------------

        /**
         * The section name for the bulk upload form.
         *
         * @var string $section_name
         */
        protected $section_name;

        /**
         * The group name for the bulk upload form.
         * @var string $group_name
         */
        protected $group_name;

        //-------------------------
        // Methods
        //-------------------------

        /**
         * Setup the category import class to extend the base CSVImport constructor.
         *
         * @param mixed $params
         */
        function __construct($params) {
            parent::__construct($params);

            // Set inherited properties specific to this class.
            //
            $this->firstline_has_fieldname  = true;
            $this->skip_firstline = true;

            // Add filters and hooks for this class.
            //
            add_filter('slp_csv_default_fieldnames' , array($this,'filter_SetDefaultFieldNames' ));
            add_action('slp_csv_processing'         , array($this,'action_ProcessCSVFile'       ));
            add_filter('slp_csv_processing_messages', array($this,'filter_SetMessages'          ));
        }

        /**
         * Process the lines of the CSV file.
         */
        function action_ProcessCSVFile() {

            // Get the incoming data field count.
            //
            $num = count($this->data);

            // Too many columns, get out.
            //
            if ($num > $this->maxcols) {
                 print "<div class='updated fade'>"                                             .
                    __('CSV Records has too many fields.','csa-slp-tagalong') . ' '                  .
                    sprintf(__('Got %d expected less than %d.', 'csa-slp-tagalong'),$num,$this->maxcols)   .
                    '</div>';
                 return;
            }


            $named_data = array();
            for ($fldno=0; $fldno < $num; $fldno++) {
                $named_data[$this->fieldnames[$fldno]] = $this->data[$fldno];
            }

            $resultOfAdd = $this->create_StoreCategory($named_data);

            // Update processing counts.
            //
            if (isset($this->processing_counts[$resultOfAdd])) {
                $this->processing_counts[$resultOfAdd]++;
            }
        }

        /**
         * Add the bulk upload form to add locations.
         */
        function create_BulkUploadForm() {

            // Shorthand
            $ml_settings = $this->parent->settings;

            // Add help text
            //
            $ml_settings->add_ItemToGroup(
                    array(
                        'section'       => $this->section_name        ,
                        'group'         => $this->group_name       ,
                        'type'          => 'subheader'      ,
                        'label'         => __('Bulk Import for Categories','csa-slp-tagalong'),
                        'show_label'    => false
                    ));

            // Form Start with Media Input
            //
            $upload_on_click = "jQuery('#tagalong_settings [name=\'action\']').attr('value','import');";
            $ml_settings->add_ItemToGroup(
                    array(
                        'section'       => $this->section_name        ,
                        'group'         => $this->group_name       ,
                        'type'          => 'custom'         ,
                        'show_label'    => false            ,
                        'custom'         =>
                            '<input type="file" name="csvfile" value="" id="bulk_file" size="40"><br/><br/>'    .
                            "<input type='submit' "                                                             .
                                "value='" . __('Upload Categories', 'csa-slp-tagalong') . "' "                  .
                                'onclick="'.$upload_on_click.'" ' .
                                "class='button-primary category_import' "                                       .
                                ">"
                        )
                    );
        }

        /**
         * Create the store categories from the CSV data.
         *
         * categoryData elements include:
         * - category <string> the category name with :: to separate parents and children
         * - description <string> description of the category
         * - slug <string> which slug to use
         * - medium_icon <string> the URL to the icon file
         * - map_marker <string> the URL for the map marker file
         *
         * @param string[] $categoryData
         * @return string action taken
         */
        function create_StoreCategory($categoryData) {
            if (empty($categoryData['category'])) { return 'skipped'; }
            $return_value = 'exists';

            $category_name_list = preg_split('/::/',$categoryData['category']);
            $category_count = count($category_name_list);
            $new_category['term_id'] = 0;
            $current_category_level = 1;
            foreach ($category_name_list as $category_name) {
                
                // Is the the last category in the list (no preceding ::)
                //
                $is_last_category = ($current_category_level++ === $category_count);

                // Setup extra category data like parent pointer, description, and slug
                //
                $extra_category_data = array();
                if ( $is_last_category && ! empty( $categoryData['slug'] ) ) {
                    $extra_category_data['slug'] = $categoryData['slug'];
                }
                $extra_category_data['parent'] = $new_category['term_id'];
                $extra_category_data['description'] =
                    ($is_last_category && !empty($categoryData['description']))    ?
                        $categoryData['description']                            :
                        $category_name                                          ;

                // Add the new category if it does not exist.
                //
                $new_category = term_exists($category_name,'stores');
                if ( !isset($new_category['term_id']) || ( $new_category['term_id'] === 0 ) ) {
                    $new_category =
                        wp_insert_term(
                            $category_name,
                            'stores',
                            $extra_category_data
                        );
                    $return_value = 'added';
                }
            }
            return $return_value;
        }

        /**
         * Set the process count output strings the users sees after an upload.
         *
         * @param string[] $message_array
         */
        function filter_SetMessages($message_array) {
            return array_merge(
                    $message_array,
                    array(
                        'added'             => __(' new categories added.'                                                   ,'csa-slp-tagalong'),
                        'exists'            => __(' pre-existing categories skipped.'                                        ,'csa-slp-tagalong'),
                        'not_updated'       => __(' categories did not need to be updated.'                                  ,'csa-slp-tagalong'),
                        'skipped'           => __(' categories were skipped due to duplicate name and address information.'  ,'csa-slp-tagalong'),
                        'updated'           => __(' categories were updated.'                                                ,'csa-slp-tagalong'),
                    )
                );
        }

        /**
         * Set the default field names if the CSV Import header is not provided.
         *
         * @param string[] $name_array
         */
        function filter_SetDefaultFieldNames($name_array) {
            return array_merge(
                    $name_array,
                    array(
                        'category','description','slug','medium-icon','map-marker', 'category_url', 'url_target' , 'rank'
                    )
                );
        }
    }
}