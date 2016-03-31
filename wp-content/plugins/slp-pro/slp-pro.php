<?php

/**
 * Plugin Name: Store Locator Plus : Pro Pack
 * Plugin URI: http://www.charlestonsw.com/product/store-locator-plus/
 * Description: A premium add-on pack for Store Locator Plus that provides more admin power tools for wrangling locations.
 * Version: 4.2.02
 * Author: Charleston Software Associates
 * Author URI: http://charlestonsw.com/
 * Requires at least: 3.3
 * Tested up to : 4.0
 *
 * Text Domain: csa-slp-pro
 * Domain Path: /languages/
 */

// Exit if access directly, dang hackers
if (!defined('ABSPATH')) {
    exit;
} 
// No SLP? Get out...
//
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if (!function_exists('is_plugin_active') || !is_plugin_active('store-locator-le/store-locator-le.php')) {
    return;
}

// Make sure the class is only defined once.
//
if (!class_exists('SLPPro')) {
    require_once( WP_PLUGIN_DIR . '/store-locator-le/include/base_class.addon.php');

    /**
     * The Pro Pack Add-On Pack for Store Locator Plus.
     *
     * @package StoreLocatorPlus\ProPack
     * @author Lance Cleveland <lance@charlestonsw.com>
     * @copyright 2014 Charleston Software Associates, LLC
     */
    class SLPPro extends SLP_BaseClass_Addon {

        //-------------------------------------
        // Properties
        //-------------------------------------

        /**
         * @var \SLPPro_Admin
         */
        var $admin;

        /**
         * Settable options for this plugin. (Does NOT go into main plugin JavaScript)
         *
         * Admin Panel Settings
         * o highlight_uncoded if on the non-geocoded locations are highlighted on the manage locations table.
         *
         * UI View
         * o layout the overall locator page layout, uses special shortcodes, default set by base plugin \SLPlus class.
         *
         * UI Search
         * o tag_label text that appears before the form label
         * o tag_selector style of search form input 'none','hidden','dropdown','textinput'
         * o tag_selections list of drop down selections
         *
         * UI Results
         * o tag_output_processing how tag data should be pre-processed when sending JSONP response to front end UI.
         *
         * CSV Processing
         * o csv_duplicates_handling = how to handle incoming records that match name + add/add2/city/state/zip/country
         * oo add = add duplicate records (default)
         * oo skip = skip over duplicate records
         * oo update = update duplicate records, requires id field in csv file to update name or address fields
         *
         * Program Control Settings
         * o installed_version - set when the plugin is activated or updated
         *
         * @var mixed[] $options
         */
        public $options = array(
            'background_processing'         => '0',
            'csv_file_url'                  => '',
            'csv_first_line_has_field_name' => '1',
            'csv_skip_first_line'           => '0',
            'csv_skip_geocoding'            => '0',
            'csv_duplicates_handling'       => 'update',
            'custom_css'                    => '',
            'highlight_uncoded'             => '1',
            'installed_version'             => '',
            'layout'                        => '',
            'tag_label'                     => '',
            'tag_selector'                  => 'none',
            'tag_selections'                => '',
            'tag_output_processing'         => 'as_entered',
            'reporting_enabled'             => '0'
        );

        //------------------------------------------------------
        // METHODS
        //------------------------------------------------------

        /**
         * Invoke the plugin.
         *
         * This ensures a singleton of this plugin.
         *
         * @static
         */
        public static function init() {
            static $instance = false;
            if (!$instance) {
                load_plugin_textdomain('csa-slp-pro', false, dirname(plugin_basename(__FILE__)) . '/languages/');
                $instance = new SLPPro(
                    array(
                        'version'               => '4.2.02'                                    ,
                        'min_slp_version'       => '4.2.07'                                 ,

                        'name'                  => __('Pro Pack', 'csa-slp-pro')            ,
                        'slug'                  => plugin_basename(__FILE__)                ,
                        'option_name'           => 'csl-slplus-PRO-options'                 ,
                        'metadata'              => get_plugin_data(__FILE__, false, false)  ,

                        'url'                   => plugins_url('', __FILE__)                ,
                        'dir'                   => plugin_dir_path(__FILE__)                ,

                        'activation_class_name'     => 'SLPPro_Activation'                  ,
                        'admin_class_name'          => 'SLPPro_Admin'                       ,
                        'ajax_class_name'           => 'SLPPro_AJAX'                        ,
                        'userinterface_class_name'  => 'SLPPro_UI'                          ,
                    )
                );
            }
            return $instance;
        }

        /**
         * Convert an array to CSV.
         *
         * @param array[] $data
         * @return string
         */
        static function array_to_CSV($data) {
            $outstream = fopen("php://temp", 'r+');
            fputcsv($outstream, $data, ',', '"');
            rewind($outstream);
            $csv = fgets($outstream);
            fclose($outstream);
            return $csv;
        }

        /**
         * Create a Map Settings Debug My Plugin panel.
         *
         * @return null
         */
        static function create_DMPPanels() {
            if (!isset($GLOBALS['DebugMyPlugin'])) {
                return;
            }
            if (class_exists('DMPPanelSLPPro') == false) {
                require_once(plugin_dir_path(__FILE__) . 'include/class.dmppanels.php');
            }
            $GLOBALS['DebugMyPlugin']->panels['slp.pro'] = new DMPPanelSLPPro();
        }

        /**
         * Process incoming AJAX request to download the CSV file.
         * TODO: use locations extended class
         */
        static function ajax_downloadLocationCSV($params) {
            if ( ! class_exists( 'CSVExportLocations' ) ) {
                require_once(plugin_dir_path(__FILE__) . 'include/class.csvexport.locations.php');
            }

            // If allowed, override the PHP execution time limit.
            //
            if ( ! ini_get('safe_mode') ) {
                ini_set( 'max_execution_time' , 600 );
                set_time_limit(600);
            }

            global $slplus_plugin;
            if ( ! is_a($slplus_plugin, 'SLPlus' ) ) {
                $slp_class_file = str_replace('slp-pro/slp-pro.php','store-locator-le/include/class.slplus.php',__FILE__);
                require_once($slp_class_file);
                $slplus_plugin = new SLPlus();                          
            }
            
            $csvLocationExporter = new CSVExportLocations(array(
                'slplus' => $slplus_plugin,
                'type' => 'Locations'
            ));
            $csvLocationExporter->do_SendFile();
        }

        /**
         * Process incoming AJAX request to download the CSV file.
         */
        static function ajax_downloadReportCSV() {
            // CSV Header
            header('Content-Description: File Transfer');
            header('Content-Disposition: attachment; filename=slplus_' . $_REQUEST['filename'] . '.csv');
            header('Content-Type: application/csv;');
            header('Pragma: no-cache');
            header('Expires: 0');

            // Setup our processing vars
            //
            global $wpdb;
            $query = $_REQUEST['query'];

            // All records - revise query
            //
            if (isset($_REQUEST['all']) && ($_REQUEST['all'] == 'true')) {
                $query = preg_replace('/\s+LIMIT \d+(\s+|$)/', '', $query);
            }

            $slpQueryTable = $wpdb->prefix . 'slp_rep_query';
            $slpResultsTable = $wpdb->prefix . 'slp_rep_query_results';
            $slpLocationsTable = $wpdb->prefix . 'store_locator';

            $expr = "/,(?=(?:[^\"]*\"[^\"]*\")*(?![^\"]*\"))/";
            $parts = preg_split($expr, trim(html_entity_decode($query, ENT_QUOTES)));
            $parts = preg_replace("/^\"(.*)\"$/", "$1", $parts);

            // Return the address in CSV format from the reports
            //
            if ($parts[0] === 'addr') {
                $slpReportStartDate = $parts[1];
                $slpReportEndDate = $parts[2];

                // Only Digits Here Please
                //
                $slpReportLimit = preg_replace('/[^0-9]/', '', $parts[3]);

                $query = "SELECT slp_repq_address, count(*)  as QueryCount FROM $slpQueryTable " .
                        "WHERE slp_repq_time > %s AND " .
                        "      slp_repq_time <= %s " .
                        "GROUP BY slp_repq_address " .
                        "ORDER BY QueryCount DESC " .
                        "LIMIT %d"
                ;
                $queryParms = array(
                    $slpReportStartDate,
                    $slpReportEndDate,
                    $slpReportLimit
                );

                // Return the locations searches in CSV format from the reports
            //
            } else if ($parts[0] === 'top') {
                $slpReportStartDate = $parts[1];
                $slpReportEndDate = $parts[2];

                // Only Digits Here Please
                //
                $slpReportLimit = preg_replace('/[^0-9]/', '', $parts[3]);

                $query = "SELECT sl_store,sl_city,sl_state, sl_zip, sl_tags, count(*) as ResultCount " .
                        "FROM $slpResultsTable res " .
                        "LEFT JOIN $slpLocationsTable sl " .
                        "ON (res.sl_id = sl.sl_id) " .
                        "LEFT JOIN $slpQueryTable qry " .
                        "ON (res.slp_repq_id = qry.slp_repq_id) " .
                        "WHERE slp_repq_time > %s AND slp_repq_time <= %s " .
                        "GROUP BY sl_store,sl_city,sl_state,sl_zip,sl_tags " .
                        "ORDER BY ResultCount DESC " .
                        "LIMIT %d"
                ;
                $queryParms = array(
                    $slpReportStartDate,
                    $slpReportEndDate,
                    $slpReportLimit
                );

                // Not Locations (top) or addresses entered in search
                // short circuit...
            //
            } else {
                die(__("Cheatin' huh!", 'csa-slp-pro'));
            }

            // No parms array?  GTFO
            //
            if (!is_array($queryParms)) {
                die(__("Cheatin' huh!", 'csa-slp-pro'));
            }


            // Run the query & output the data in a CSV
            $thisDataset = $wpdb->get_results($wpdb->prepare($query, $queryParms), ARRAY_N);


            // Sorting
            // The sort comes in based on the display table column order which
            // matches the query output column order listed here.
            //
            // It is a paired array, first number is the column number (zero offset)
            // second number is the sort order [0=ascending, 1=descending]
            //
            // The sort needs to happen AFTER the select.
            //

            // Get our sort array
            //
            $thisSort = explode(',', $_REQUEST['sort']);

            // Build our array_multisort command and our sort index/sort order arrays
            // we will need this later for helping do a multi-dimensional sort
            //
            $sob = 'sort';
            $amsstring = '';
            $sortarrayindex = 0;
            foreach ($thisSort as $sl_value) {
                if ($sob == 'sort') {
                    $sort[] = $sl_value;
                    $amsstring .= '$s[' . $sortarrayindex++ . '], ';
                    $sob = 'order';
                } else {
                    $order[] = $sl_value;
                    $amsstring .= ($sl_value == 0) ? 'SORT_ASC, ' : 'SORT_DESC, ';
                    $sob = 'sort';
                }
            }
            $amsstring .= '$thisDataset';

            // Now that we have our sort arrays and commands,
            // build the indexes that will be used to do the
            // multi-dimensional sort
            //
            foreach ($thisDataset as $key => $row) {
                $sortarrayindex = 0;
                foreach ($sort as $column) {
                    $s[$sortarrayindex++][$key] = $row[$column];
                }
            }

            // Now do the multidimensional sort
            //
            // This will sort using the first array ($s[0] we built in the above 2 steps)
            // to determine what order to put the "records" (the outter array $thisDataSet)
            // into.
            //
            // If there are secondary arrays ($s[1..n] as built above) we then further
            // refine the sort using these secondary arrays.  Think of them as the 2nd
            // through nth columns in a multi-column sort on a spreadsheet.
            //
            // This exactly mimics the jQuery sorts that manage our tables on the HTML
            // page.
            //

            //array_multisort($amsstring);
            // Output the sorted CSV strings
            // This simply iterates through our newly sorted array of records we
            // got from the DB and writes them out in CSV format for download.
            //
            foreach ($thisDataset as $thisDatapoint) {
                print SLPPro::array_to_CSV($thisDatapoint);
            }

            // Get outta here
            die();
        }

        /**
         * Simplify the plugin debugMP interface.
         *
         * @param string $type
         * @param string $hdr
         * @param string $msg
         */
        function debugMP($type, $hdr, $msg = '') {
            if (!is_object($this->slplus)) {
                return;
            }
            $this->slplus->debugMP('slp.pro', $type, $hdr, $msg, NULL, NULL, true);
        }

        /**
         * Set the admin menu items.
         *
         * @param mixed[] $menuItems
         * @return mixed[]
         */
        public function filter_AddMenuItems( $menuItems ) {
            $this->createobject_Admin();
            $this->admin_menu_entries = array(
                array(
                    'label'         => __('Reports', 'csa-slp-pro'),
                    'slug'          => 'slp_reports',
                    'class'         => $this->admin->reports,
                    'function'      => 'render_ReportsTab'
                ),
            );
            return parent::filter_AddMenuItems( $menuItems );
        }

        /**
         * Process a cron job.
         *
         * WordPress is not a "time perfect" cron processor.   It will fire the event the next time a visitor
         * comes to the site AFTER the specified cron job time.
         *
         * Action Parameter
         * - 'import_csv' : import the csv locations file, params needs to be a file_meta named array
         *
         * @param $action
         * @param $params
         */
        static function process_cron_job( $action , $params ) {
            if ( class_exists( 'SLPPro_Cron' ) == false ) {
                require_once(plugin_dir_path(__FILE__) . 'include/class.cron.php');
            }

            $cron_job = new SLPPro_Cron(
                    array(
                        'action'        => $action ,
                        'action_params' => $params
                    )
                );

            // Run this after all other SLP inits have happened, especially hooking up Pro Pack
            //
            add_action('slp_init_complete', array($cron_job, 'process_action') , 99);
        }

    }

    // Hook to invoke the plugin.
    //
    add_action('init', array('SLPPro', 'init'));

    // AJAX Listeners
    //
    add_action('wp_ajax_slp_download_report_csv', array('SLPPro', 'ajax_downloadReportCSV'));
    add_action('wp_ajax_slp_download_locations_csv', array('SLPPro', 'ajax_downloadLocationCSV'));

    // CRON Jobs
    //
    add_action('cron_csv_import' , array( 'SLPPro' , 'process_cron_job') , 10 , 2 );

    // DMP
    //
    add_action('dmp_addpanel', array('SLPPro', 'create_DMPPanels'));
}
// Dad. Explorer. Rum Lover. Code Geek. Not necessarily in that order.
