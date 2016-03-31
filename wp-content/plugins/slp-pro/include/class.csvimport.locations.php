<?php
if (!defined( 'ABSPATH'     )) { exit;   } // Exit if accessed directly, dang hackers

// Make sure the class is only defined once.
//
if (!class_exists('CSVImport')) {
    require_once(SLPLUS_PLUGINDIR . '/include/class.csvimport.php');
}

// Make sure the class is only defined once.
//
if (!class_exists('CSVImportLocations')) {

    /**
     * CSV Import of Locations
     *
     * @package StoreLocatorPlus\ProPack\CSVImportLocations
     * @author Lance Cleveland <lance@charlestonsw.com>
     * @copyright 2013 Charleston Software Associates, LLC
     */
    class CSVImportLocations extends CSVImport {

        //----------------------------------------------------------------------------
        // Properties
        //----------------------------------------------------------------------------

        /**
         * @var \SLPPro
         */
        public $addon;

        /**
         *
         * @var _FILES
         */
        public $file_meta;

        /**
         * Should goecoding be skipped?
         *
         * @var boolean $skip_geocoding
         */
        private $skip_geocoding = false;

        /**
         * The manage locations settings interface object.
         *
         * @var \wpCSL_settings__slplus
         */
        private $settings;

        //----------------------------------------------------------------------------
        // Methods
        //----------------------------------------------------------------------------

        /**
         * Setup a standard CSV Import object and attach the processing method and data filters.
         *
         * @param mixed $params
         */
        function __construct($params) {
            parent::__construct($params);

            $this->settings = $this->slplus->AdminUI->ManageLocations->settings;

            // Set private properties that are only for this class.
            //
            $this->skip_geocoding= $this->slplus->is_CheckTrue( $this->addon->options['csv_skip_geocoding' ] );

            // Set inherited properties specific to this class.
            //
            $this->strip_prefix = 'sl_';
            $this->firstline_has_fieldname  = $this->slplus->is_CheckTrue( $this->addon->options['csv_first_line_has_field_name'] );
            $this->skip_firstline           = $this->firstline_has_fieldname || $this->slplus->is_CheckTrue( $this->addon->options['csv_skip_first_line'] );

            // Add filters and hooks for this class.
            //
            add_filter('slp_csv_processing_messages', array($this,'filter_SetMessages'          ));
            add_filter('slp_csv_default_fieldnames' , array($this,'filter_SetDefaultFieldNames' ));
            add_action('slp_csv_processing'         , array($this,'action_ProcessCSVFile'       ));
        }

        /**
         * Process the lines of the CSV file.
         */
        function action_ProcessCSVFile() {
            $num = count($this->data);
            $locationData = array();
            if ($num <= $this->maxcols) {
                for ($fldno=0; $fldno < $num; $fldno++) {
                    $locationData[$this->fieldnames[$fldno]] = $this->data[$fldno];
                }

                // Record Add/Update
                //

                // FILTER: slp_csv_locationdata
                // Pre-location import processing.
                //
                $locationData = apply_filters('slp_csv_locationdata',$locationData);

                // Go add the CSV Data to the locations table.
                //
                $resultOfAdd = $this->slplus->AdminUI->ManageLocations->location_AddToDatabase(
                        $locationData,
                        $this->addon->options['csv_duplicates_handling'],
                        $this->skip_geocoding ||
                            (
                            isset     ($locationData['sl_longitude']) && isset     ($locationData['sl_latitude']) &&
                            is_numeric($locationData['sl_longitude']) && is_numeric($locationData['sl_latitude'])
                            )
                        );

                // FILTER: slp_csv_locationdata_added
                // Post-location import processing.
                //
                apply_filters('slp_csv_locationdata_added',$locationData, $resultOfAdd);

                // Update processing counts.
                //
                $this->processing_counts[$resultOfAdd]++;
            } else {
                 print "<div class='updated fade'>"                                             .
                    __('CSV Records has too many fields.','csa-slp-pro') . ' '                  .
                    sprintf(__('Got %d expected less than %d.', 'csa-slp-pro'),$num,$this->maxcols)   .
                    '</div>';
            }
        }

        /**
         * Add the File Settings panel to the Import section of Locations.
         *
         * @param $section_name
         */
        private function  add_FileSettingsPanel( $section_name ) {
            $panel_name = __( 'File Settings', 'csa-slp-pro' );

            // Skip First Line
            //
            $this->settings->add_ItemToGroup(
                array(
                    'section'       => $section_name     ,
                    'group'         => $panel_name       ,
                    'type'          => 'checkbox'        ,
                    'setting'       => 'PRO-options[csv_skip_first_line]',
                    'value'         => $this->addon->options['csv_skip_first_line'],
                    'label'         => __('Skip First Line','csa-slp-pro'),
                    'description'   => __('Skip the first line of the import file.','csa-slp-pro')
                )
            );

            // First Line Has Field Name
            //
            $this->settings->add_ItemToGroup(
                array(
                    'section'       => $section_name     ,
                    'group'         => $panel_name       ,
                    'type'          => 'checkbox'       ,
                    'setting'       => 'PRO-options[csv_first_line_has_field_name]',
                    'label'         => __('First Line Has Field Name','csa-slp-pro'),
                    'value'         => $this->addon->options['csv_first_line_has_field_name'],
                    'description'   =>
                        __('Check this if the first line contains the field names.','csa-slp-pro') . ' ' .
                        sprintf(__('Text must match the <a href="%s">approved field name list</a>.','csa-slp-pro'),$this->slplus->support_url)
                )
            );

            // Skip Geocoding
            //
            $this->settings->add_ItemToGroup(
                array(
                    'section'       => $section_name     ,
                    'group'         => $panel_name       ,
                    'type'          => 'checkbox'       ,
                    'setting'       => 'PRO-options[csv_skip_geocoding]',
                    'value'         => $this->addon->options['csv_skip_geocoding'],
                    'label'         => __('Skip Geocoding','csa-slp-pro'),
                    'description'   =>
                        __('Do not check with the Geocoding service to get latitude/longitude.  Locations without a latitude/longitude will NOT appear on map base searches.','csa-slp-pro')
                )
            );


            // Background Processing
            //
            // TODO : Finish background processing implementation.
            //
//            $this->settings->add_ItemToGroup(
//                array(
//                    'section'       => $section_name     ,
//                    'group'         => $panel_name       ,
//                    'type'          => 'checkbox'       ,
//                    'setting'       => 'PRO-options[background_processing]',
//                    'value'         => $this->addon->options['background_processing'],
//                    'label'         => __('Background Processing','csa-slp-pro'),
//                    'description'   =>
//                        __('When checked the import will run as a WordPress cron job.  ', 'csa-slp-pro') .
//                        __('WP cron executes on the next visit to your website.  ', 'csa-slp-pro') .
//                        __('This frees up the admin UI for other work while locations are loaded.','csa-slp-pro')
//                )
//            );

            // Duplicates Handling
            //
            $this->settings->add_ItemToGroup(
                array(
                    'section'       => $section_name     ,
                    'group'         => $panel_name       ,
                    'type'          => 'dropdown'       ,
                    'setting'       => 'PRO-options[csv_duplicates_handling]',
                    'selectedVal'   => $this->addon->options['csv_duplicates_handling'],
                    'label'         => __('Duplicates Handling','csa-slp-pro'),
                    'description'   =>
                        __('How should duplicates be handled? ','csa-slp-pro') .
                        __('Duplicates are records that match on name and complete address with country. ','csa-slp-pro') .
                        __('Add (default) will add new records when duplicates are encountered. ','csa-slp-pro') . '<br/>' .
                        __('Skip will not process duplicate records. ','csa-slp-pro') . '<br/>' .
                        __('Update will update duplicate records. ','csa-slp-pro') .
                        __('To update name and address fields the CSV must have the ID column with the ID of the existing location.','csa-slp-pro')
                ,
                    'custom'    =>
                        array(
                            array(
                                'label'     => __('Add','csa-slp-pro'),
                                'value'     =>'add',
                            ),
                            array(
                                'label'     => __('Skip','csa-slp-pro'),
                                'value'     =>'skip',
                            ),
                            array(
                                'label'     => __('Update','csa-slp-pro'),
                                'value'     =>'update',
                            ),
                        )
                )
            );

            // Add help text
            //
            $this->settings->add_ItemToGroup(
                array(
                    'section'       => $section_name    ,
                    'group'         => $panel_name      ,
                    'type'          => 'subheader'      ,
                    'label'         => '',
                    'description'   =>
                        sprintf(__('See the %s for more details on the import format.','csa-slp-pro'),
                            sprintf('<a href="%slocations/bulk-data-import/">',$this->slplus->support_url) .
                            __('online documentation','csa-slp-pro') .
                            '</a>'
                        ),
                    'show_label'    => false
                ));
        }

        /**
         * Local File Import Panel.
         *
         * @param $section_name
         */
        function add_UploadCSVPanel( $section_name ) {
            $panel_name = __('Upload CSV File', 'csa-slp-pro');

            // Form Start with Media Input
            //
            $this->settings->add_ItemToGroup(
                array(
                    'section'       => $section_name    ,
                    'group'         => $panel_name      ,
                    'type'          => 'custom'         ,
                    'show_label'    => false            ,
                    'custom'         =>
                            '<input type="file" name="csvfile" value="" id="bulk_file" size="40">'              .
                            "<input type='submit' value='".__('Upload Locations', 'csa-slp-pro')."' "           .
                            "class='button-primary'>"
                )
            );
        }


        /**
         * Remote File Import Panel.
         *
         * @param $section_name
         */
        function add_RemoteFileImportPanel( $section_name ) {
            $panel_name = __('File Import', 'csa-slp-pro');

            // Remove File URL
            //
            $this->settings->add_ItemToGroup(
                array(
                    'section'       => $section_name                            ,
                    'group'         => $panel_name                              ,
                    'label'         => __('CSV File URL','csa-slp-pro')         ,
                    'setting'       => 'csv_file_url'                           ,
                    'use_prefix'    => false                                    ,
                    'type'          => 'text'                                   ,
                    'value'         => $this->addon->options['csv_file_url']    ,
                    'description'   =>
                        __('Enter a full URL for a CSV file you wish to import', 'csa-slp-pro')
                )
            );


            // Form Start with Media Input
            //
            $this->settings->add_ItemToGroup(
                array(
                    'section'       => $section_name    ,
                    'group'         => $panel_name      ,
                    'type'          => 'custom'         ,
                    'show_label'    => false            ,
                    'custom'         =>
                        "<input type='submit' value='".__('Import Locations', 'csa-slp-pro')."' "           .
                        "class='button-primary'>"
                )
            );

        }

        /**
         * Add the bulk upload form to add locations.
         */
        function create_BulkUploadForm() {
            $section_name = __('Import','csa-slp-pro');

            $this->settings->add_section(
                array(
                    'name'          => $section_name ,

                    'opening_html'  =>
                        "<form id='importForm' name='importForm' method='post' enctype='multipart/form-data'>"  .
                        "<input type='hidden' name='act' id='act' value='import' />" ,

                    'closing_html' =>
                        '</form>'
                )
            );

            // File Settings Panel
            //
            $this->add_FileSettingsPanel( $section_name );

            // Upload CSV Local
            //
            $this->add_UploadCSVPanel( $section_name );

            // File Import Remoete
            //
            $this->add_RemoteFileImportPanel( $section_name );

        }

        /**
         * Set the process count output strings the users sees after an upload.
         *
         * @param string[] $message_array
         * @return mixed[]
         */
        function filter_SetMessages($message_array) {
            return array_merge(
                    $message_array,
                    array(
                        'added'             => __(' new locations added.'                                                   ,'csa-slp-pro'),
                        'location_exists'   => __(' pre-existing locations skipped.'                                        ,'csa-slp-pro'),
                        'not_updated'       => __(' locations did not need to be updated.'                                  ,'csa-slp-pro'),
                        'skipped'           => __(' locations were skipped due to duplicate name and address information.'  ,'csa-slp-pro'),
                        'updated'           => __(' locations were updated.'                                                ,'csa-slp-pro'),
                    )
                );
        }

        /**
         * Set the default field names if the CSV Import header is not provided.
         *
         * Default:
         * 'sl_store'   [ 0],'sl_address'  [ 1],'sl_address2'[ 2],'sl_city'       [ 3],'sl_state'[ 4],
         * 'sl_zip'     [ 5],'sl_country'  [ 6],'sl_tags'    [ 7],'sl_description'[ 8],'sl_url'  [ 9],
         * 'sl_hours'   [10],'sl_phone'    [11],'sl_email'   [12],'sl_image'      [13],'sl_fax'  [14],
         * 'sl_latitude'[15],'sl_longitude'[16],'sl_private' [17],'sl_neat_title' [18]
         *
         * @param string[] $name_array
         * @return string[]
         */
        function filter_SetDefaultFieldNames($name_array) {
            return array_merge(
                    $name_array,
                    array(
                        'sl_store','sl_address','sl_address2','sl_city','sl_state',
                        'sl_zip','sl_country','sl_tags','sl_description','sl_url',
                        'sl_hours','sl_phone','sl_email','sl_image','sl_fax',
                        'sl_latitude','sl_longitude','sl_private','sl_neat_title'
                    )
                );
        }

        /**
         * Set file meta for the import.
         *
         * Use the standard browser file upload objects $_FILES if set.
         *
         * If not set check for a remote file URL and use that.
         *
         */
        function set_FileMeta( $file_meta = null ) {

            // Browser File Upload
            //
            if ( isset( $_FILES ) && ( ! empty( $_FILES['csvfile']['name'] ) ) ) {
                $this->file_meta = $_FILES;
                return;
            }

            // Remote File URL
            //
            if (
                isset( $_REQUEST['csv_file_url'] )
            ) {
                $ftp_file = file_get_contents( $_REQUEST['csv_file_url'] );

                $local_file = wp_tempnam();

                file_put_contents( $local_file , $ftp_file );

                $this->file_meta['csvfile'] = array(
                    'name'      => 'slp_locations.csv'                  ,
                    'type'      => 'text/csv'                           ,
                    'tmp_name'  => $local_file                          ,
                    'error'     => ( is_bool($ftp_file) ? '4' : '0')    ,
                    'size'      => strlen($ftp_file)                    ,
                    'source'    => 'direct_url'                         ,
                );
            }

            // Passed via Cron
            //
            if (
                $file_meta !== null
            ) {
                $this->file_meta = $file_meta;
            }
        }

        /**
         * Process the file being imported.
         *
         * cron_csv_import action takes 2 parameters:
         * param 1: the action to perform
         * param 2: the params to be sent to the action processor
         *
         * @param mixed[] $file_meta the details about the file being processed.
         */
        public function process_File( $file_meta = NULL ) {

            // Cron Job CSV Import Processing
            //
            if ( $this->slplus->is_CheckTrue( $this->addon->options['background_processing'] ) ) {
                wp_schedule_single_event(time(), 'cron_csv_import', array('import_csv', $this->file_meta));
                return;
            }

            // Immediate Processing
            //
            parent::process_File( $file_meta );
        }
    }
}

