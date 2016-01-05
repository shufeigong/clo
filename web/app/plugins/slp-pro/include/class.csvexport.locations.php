<?php
if (!defined( 'ABSPATH'     )) { exit;   } // Exit if accessed directly, dang hackers

// Make sure the class is only defined once.
//
if (!class_exists('CSVExport')) {
    require_once('class.csvexport.php');
    
    /**
     * CSV Export for Pro Pack
     *
     * @package StoreLocatorPlus\ProPack\CSVExportLocations
     * @author Lance Cleveland <lance@charlestonsw.com>
     * @copyright 2014 Charleston Software Associates, LLC
     */
    class CSVExportLocations extends CSVExport {


        //-------------------------------------
        // Properties
        //-------------------------------------

        /**
         * The fields we want to export.
         *
         * @var string[] $dbFields
         */
        private $dbFields = array(
                'sl_id',
                'sl_store',
                'sl_address',
                'sl_address2',
                'sl_city',
                'sl_state',
                'sl_zip',
                'sl_country',
                'sl_latitude',
                'sl_longitude',
                'sl_tags',
                'sl_description',
                'sl_email',
                'sl_url',
                'sl_hours',
                'sl_phone',
                'sl_fax',
                'sl_image',
            );

        /**
         * The incoming AJAX form data.
         *
         * @var mixed[]
         */
        private $formdata;

        /**
         *
         * @param mixed[] $params
         */
        function __construct($params) {
            parent::__construct($params);
            $this->type='Locations';

            // FILTER: slp-pro-dbfields
            $this->dbFields=apply_filters('slp-pro-dbfields',$this->dbFields);
        }

        /**
         * Send Locations
         */
        function do_SendLocations() {
            
            // Need to manually set this up for AJAX processing.
            //
            if (!class_exists('SLPProAdminLocationFilters')) {
                require_once('class.admin.location.filters.php');
            }
            $admin_location_filters = 
                new SLPProAdminLocationFilters( array(
                    'addon'     =>  $this->addon,
                    'slplus'    =>  $this->slplus
                ));
            
            // Get the SQL command and where params
            //
            list($sqlCommand, $sqlParams) = $admin_location_filters->create_LocationSQLCommand( $_REQUEST );

            // Open stdout
            // Byte Order Mark (BOM) for UTF-8 is \xEF\xBB\xBF
            // Byte Order Mark (BOM) for UTF-16 is \xFE\xFF
            // @see http://en.wikipedia.org/wiki/Byte_order_mark#UTF-8
            $stdout = fopen('php://output','w');
            fputs($stdout, "\xEF\xBB\xBF");
            
            // Export Header
            //
            fputcsv($stdout,$this->dbFields);

            // Export records
            //
            $offset=0;
            while ($locationArray = $this->slplus->database->get_Record( $sqlCommand , $sqlParams , $offset++ )) {
                // FILTER: slp-pro-csvexport
                $locationArray = apply_filters('slp-pro-csvexport',$locationArray);
                fputcsv($stdout,array_intersect_key($locationArray,array_flip($this->dbFields)));
            }

            // Close stdout
            //
            fflush($stdout);
            fclose($stdout);
        }
    }
}
