<?php
if( !class_exists('SLPlus_AdminUI_Locations') ){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

	/**
	 * Store Locator Plus manage locations admin user interface.
	 *
	 * @property        boolean         $addingLocation     Adding a location? (default: false)
	 * @property-read   string[]        $active_columns     Active extended data columns.
	 * @property-read   array           $script_data        Things to be localized for the data tables js.
	 *
	 * @package StoreLocatorPlus\AdminUI\Locations
	 * @author Lance Cleveland <lance@charlestonsw.com>
	 * @copyright 2012-2015 Charleston Software Associates, LLC
	 *
	 * @var mixed[] $columns our column headers
	 */
	class SLPlus_AdminUI_Locations extends WP_List_Table {
		public  $addingLocation = false;
		private $active_columns;
		private $script_data = array();

		/**
		 * The current request URL without order by or sorting parameters.
		 *
		 * @var string $cleanURL
		 */
		private $cleanURL;

		/**
		 * Array of our Manage Locations interface column names.
		 *
		 * key is the field name, value is the column title
		 *
		 * @var mixed[] $columns
		 */
		public $columns = array();

		/**
		 * The current action as determined by the incoming $_REQUEST['act'] string.
		 *
		 * @var string $current_action
		 */
		public $current_action;

		/**
		 * A list of empty column field IDs.
		 *
		 * key is the field name, value is the column title
		 *
		 * @var mixed[] $columns
		 */
		private $empty_columns = array();

		/**
		 * The extended column meta data in a stdClass object.
		 *
		 * Properties:
		 * - id = the unique id for this field
		 * - field_id = the unique id as a string field_###
		 * - label = the proper case label
		 * - slug = the "slugified" version of the label
		 * - type = the field type varchar(default)/text/int/boolean
		 * - options (serialized)
		 *
		 * @var mixed[] $extended_data_info
		 */
		private $extended_data_info;

		/**
		 * Extra database where clause filters.
		 *
		 * This is leftover from legacy code where this admin ui locations class
		 * had its own custom data handler for managing where clauses and order by
		 * vs. the current data class standard sql methods.
		 *
		 * @var string
		 */
		private $extra_location_filters = '';

		/**
		 * The wpCSL settings object that helps render location settings.
		 *
		 * @var wpCSL_settings__slplus $settings
		 */
		public $settings;

		/**
		 * The SLPlus plugin object.
		 *
		 * @var SLPlus $slplus
		 */
		private $slplus;

		/**
		 *
		 * @var string $baseAdminURL
		 */
		private $baseAdminURL = '';

		/**
		 *
		 * @var string $cleanAdminURl
		 */
		private $cleanAdminURL = '';

		/**
		 * Order by field for the order by clause.
		 *
		 * @var string
		 */
		private $db_orderbyfield = 'sl_store';

		/**
		 * The manage locations URL with params we like to keep such as page number and sort order.
		 *
		 * @var string $hangoverURL
		 */
		public $hangoverURL = '';

		/**
		 * Requested sort order.
		 *
		 * @var string
		 */
		private $sort_order = 'asc';

		/**
		 * Start listing locations from this record offset.
		 *
		 * @var int
		 */
		private $start = 0;

		/**
		 * Total locations on list.
		 *
		 * @var int
		 */
		private $total_locations_shown = 0;

		//------------------------------------------------------
		// METHODS
		//------------------------------------------------------

		/**
		 * Called when this object is created.
		 */
		function __construct() {
			global $slplus_plugin;
			$this->slplus = $slplus_plugin;

			$this->set_CurrentAction();

			// If there are ANY extended data fields...
			//
			if ( $this->slplus->database->is_Extended() ) {

				// SLP Action Hooks
				//
				// slp_location_saved : update extendo data when changing a location
				//
				add_action( 'slp_location_saved', array( $this, 'action_SaveExtendedData' ) );
				add_filter( 'slp_manage_expanded_location_columns', array(
					$this,
					'add_extended_data_to_manage_locations_table'
				) );

				// If there are actual extended data records in existence.
				//
				if ( $this->slplus->database->has_extended_data() ) {
					add_action( 'slp_deletelocation_starting', array( $this, 'action_DeleteExtendedData' ) );
				}
			}

			// Set our base Admin URL
			//
			if ( isset( $_SERVER['REQUEST_URI'] ) ) {
				$this->cleanAdminURL =
					isset( $_SERVER['QUERY_STRING'] ) ?
						str_replace( '?' . $_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI'] ) :
						$_SERVER['REQUEST_URI'];

				$queryParams = array();

				// Base Admin URL = must have params
				//
				if ( isset( $_REQUEST['page'] ) ) {
					$queryParams['page'] = $_REQUEST['page'];
				}
				$this->baseAdminURL = $this->cleanAdminURL . '?' . build_query( $queryParams );


				// Hangover URL = params we like to carry around sometimes
				//
				if ( $this->current_action === 'show_all' ) {
					$_REQUEST['searchfor'] = '';
				}
				if ( isset( $_REQUEST['searchfor'] ) && ! empty( $_REQUEST['searchfor'] ) ) {
					$queryParams['searchfor'] = $_REQUEST['searchfor'];
				}
				if ( isset( $_REQUEST['start'] ) && ( (int) $_REQUEST['start'] >= 0 ) ) {
					$queryParams['start'] = $_REQUEST['start'];
				}
				if ( isset( $_REQUEST['orderBy'] ) && ! empty( $_REQUEST['orderBy'] ) ) {
					$queryParams['orderBy'] = $_REQUEST['orderBy'];
				}
				if ( isset( $_REQUEST['sortorder'] ) && ! empty( $_REQUEST['sortorder'] ) ) {
					$queryParams['sortorder'] = $this->sort_order;
				}

				$this->hangoverURL = $this->cleanAdminURL . '?' . build_query( $queryParams );

				$this->create_object_settings();
			}
		}

		/**
		 * Create and attach settings.
		 */
		function create_object_settings() {
			if ( ! isset( $this->settings ) ) {
				require_once( 'class.settings.php' );
				$this->settings = new SLP_Settings( array(
						'name'        => $this->slplus->name . __( ' - Locations', 'store-locator-le' ),
						'form_action' => $this->baseAdminURL,
						'form_name'   => 'locationForm',
						'save_text'   => ''
					)
				);
			}
		}

		/**
		 * Enqueue the dataTables JS.
		 *
		 * @see https://github.com/DataTables/DataTables
		 */
		private function enqueue_scripts() {
			if ( file_exists( SLPLUS_PLUGINDIR . 'js/jquery.dataTables.min.js' ) ) {
				wp_enqueue_script(  'jquery-ui-draggable' );
				wp_enqueue_script(  'jquery-ui-droppable' );
				wp_enqueue_script(  'slp_datatables' , SLPLUS_PLUGINURL . '/js/jquery.dataTables.min.js' );

				wp_enqueue_script(  'slp_admin_locations_manager' , SLPLUS_PLUGINURL . '/js/admin-locations-tab.js' , array( 'slp_datatables', 'jquery-ui-draggable' , 'jquery-ui-droppable' ));
				wp_localize_script( 'slp_admin_locations_manager' , 'location_manager' , $this->script_data );

				if (file_exists(SLPLUS_PLUGINDIR . 'css/admin/jquery.dataTables-slp.css')) {
					wp_enqueue_style(
						'slp_admin_locations_manager', SLPLUS_PLUGINURL . '/css/admin/jquery.dataTables-slp.css'
					);
				}

			}
		}

		/**
		 * Format the address description for the add/edit lcoation panel.
		 *
		 * @return string
		 */
		private function get_address_description() {
			if ( $this->addingLocation ) {
				$address_description = __( 'Adding a new location.', 'store-locator-le' );
			} else {
				$linked_post_message = '';
				if ( ! empty( $this->slplus->currentLocation->linked_postid ) ) {
					$linked_post_message =
						sprintf( __( 'Linked to %s page # %d', 'store-locator-le' ),
							$this->slplus->add_ons->get_product_url( 'slp-pages' ),
							$this->slplus->currentLocation->linked_postid
						);
				}

				$lat_long_message = '';
				if ( is_numeric( $this->slplus->currentLocation->latitude ) && is_numeric( $this->slplus->currentLocation->longitude ) ) {
					$lat_long_message =
						sprintf(
							__( 'Original latitude %f , longitude %f.', 'store-locator-le' ),
							$this->slplus->currentLocation->latitude,
							$this->slplus->currentLocation->longitude
						);
				}

				$address_description =
					sprintf(
						__( 'Editing location #%d %s %s', 'store-locator-le' ),
						$this->slplus->currentLocation->id,
						$linked_post_message,
						$lat_long_message
					);

			}
			$address_description .= '<br/>' .
			                        sprintf( '<a href="%s" target="csa" alt="%s" title="%s">%s</a>',
				                        'http://www.latlong.net',
				                        __( 'Latitude/Longitude lookup.', 'store-locator-le' ),
				                        __( 'Latitude/Longitude lookup.', 'store-locator-le' ),
				                        __( 'The LatLong.net website can help you locate an exact latitude/longitude.', 'store-locator-le' )
			                        );

			return "<p>{$address_description}</p>";
		}

		/**
		 * Set the columns we will render on the manage locations page.
		 */
		function get_columns() {
			$this->script_data['user_id'] = get_current_user_id();

			// For all views
			//
			$this->columns = array(
				'sl_id'          => __( 'Actions'    , 'store-locator-le' ),
				'sl_store'       => __( 'Name'       , 'store-locator-le' ),
				'sl_address'     => __( 'Address'    , 'store-locator-le' ),
				'sl_address2'    => __( 'Address 2'  , 'store-locator-le' ),
				'sl_city'        => __( 'City'       , 'store-locator-le' ),
				'sl_state'       => __( 'State'      , 'store-locator-le' ),
				'sl_zip'         => __( 'Zip'        , 'store-locator-le' ),
				'sl_country'     => __( 'Country'    , 'store-locator-le' ),
				'sl_description' => __( 'Description', 'store-locator-le' ),
				'sl_email'       => $this->slplus->WPML->get_text( 'label_email' ),
				'sl_url'         => $this->slplus->WPML->get_text( 'label_website' ),
				'sl_hours'       => $this->slplus->WPML->get_text( 'label_hours' ),
				'sl_phone'       => $this->slplus->WPML->get_text( 'label_phone' ),
				'sl_fax'         => $this->slplus->WPML->get_text( 'label_fax' ),
				'sl_image'       => $this->slplus->WPML->get_text( 'label_image' ),
			);

			/**
			 * Filter to add columns to expanded view on manage locations
			 *
			 * @filter slp_manage_expanded_location_columns
			 *
			 * @params array    $columns
			 *
			 * @deprecated use slp_manage_locations filter
			 */
			$this->columns = apply_filters( 'slp_manage_expanded_location_columns', $this->columns );

			/**
			 * Filter to add columns to expanded view on manage locations
			 *
			 * @filter slp_manage_location_columns
			 *
			 * @params array    $columns
			 */
			$this->columns = apply_filters( 'slp_manage_location_columns', $this->columns );

			return $this->columns;
		}

		/**
		 * Get a list of all, hidden and sortable columns, with filter applied
		 *
		 * @return array
		 */
		function get_column_info() {
			if ( ! isset( $this->_column_headers ) ) {
				$this->_column_headers = array(
					$this->columns,
					$this->get_columns_hidden_by_user(),
					array(),
					'sl_id'
				);
			}
			return $this->_column_headers;
		}

		/**
		 * Get columns hidden by the user.
		 */
		private function get_columns_hidden_by_user() {
			$previously_hidden_columns = get_user_meta(
				$this->script_data['user_id']  ,
				'slp_hidden_location_manager_columns' ,
				true
			);
			return (array) maybe_unserialize( $previously_hidden_columns );
		}

		/**
		 * Set the current action being executed by the plugin.
		 */
		function set_CurrentAction() {
			if ( ! isset( $_REQUEST['act'] ) ) {
				$this->current_action = '';
			} else {
				$this->current_action = strtolower( $_REQUEST['act'] );
			}

			if ( isset( $_REQUEST['sortorder'] ) ) {
				$this->sort_order = $_REQUEST['sortorder'];
			}

			// Special Processing of Actions
			//
			switch ( $this->current_action ) {
				case 'edit':
					if ( ! $this->slplus->currentLocation->isvalid_ID( null, 'id' ) ) {
						$this->current_action = 'manage';
					}
					break;

				// NO action ,can indicate over post max size
				// Test for that with REQUEST_METHOD and CONTENT_LENGTH
				case '':
					if (
						isset( $_SERVER['REQUEST_METHOD'] ) &&
						( $_SERVER['REQUEST_METHOD'] === 'POST' ) &&
						isset( $_SERVER['CONTENT_LENGTH'] ) &&
						( empty( $_POST ) )
					) {
						$max_post_size  = ini_get( 'post_max_size' );
						$content_length = $_SERVER['CONTENT_LENGTH'] / 1024 / 1024;
						if ( $content_length > $max_post_size ) {
							print "<div class='updated fade'>" .
							      sprintf(
								      __( 'It appears you tried to upload %d MiB of data but the PHP post_max_size is %d MiB.', 'store-locator-le' ),
								      $content_length,
								      $max_post_size
							      ) .
							      '<br/>' .
							      __( 'Try increasing the post_max_size setting in your php.ini file.', 'store-locator-le' ) .
							      '</div>';
						}
					}
					break;

				default:
					break;
			}
		}


		/**
		 * Add the location filter.
		 *
		 * @param $where
		 *
		 * @return string
		 */
		function set_location_filter( $where ) {

			// Support the legacy where clause filters added by the
			// slp_manage_location_where filter
			//
			if ( ! empty( $this->extra_location_filters ) ) {
				$this->slplus->database->reset_clauses();
				$where = $this->slplus->database->extend_Where( '', $this->extra_location_filters );
			}

			// Add any filters from the search box.
			//
			$search_filter = $this->set_search_filter();
			if ( ! empty( $search_filter ) ) {
				$where = $this->slplus->database->extend_Where( $where, $search_filter );
			}

			return $where;
		}

		/**
		 * Set the search filter (where clause) if the searchfor field comes in via the form post.
		 * @return string
		 */
		private function set_search_filter() {
			if ( isset( $_POST['searchfor'] ) ) {
				$clean_search_for = stripslashes_deep( trim( $_POST['searchfor'] ) );
				if ( ! empty ( $clean_search_for ) ) {
					$clean_search_for = '%%' . esc_sql( $this->slplus->db->esc_like( $clean_search_for ) ) . '%%';

					return
						sprintf(
							" CONCAT_WS(';',sl_store,sl_address,sl_address2,sl_city,sl_state,sl_zip,sl_country,sl_tags) LIKE '%s'",
							$clean_search_for
						);
				}
			}

			return '';
		}

		/**
		 * Set the locations table order by SQL command.
		 *
		 * @param $current_order_array
		 */
		function set_location_order( $current_order_array ) {

			// Sort Direction
			//
			$this->db_orderbyfield =
				( isset( $_REQUEST['orderBy'] ) && ! empty( $_REQUEST['orderBy'] ) ) ?
					$_REQUEST['orderBy'] :
					'sl_store';

			$this->slplus->database->extend_order_array( "{$this->db_orderbyfield} {$this->sort_order}" );
		}

		/**
		 * Set all the properties that manage the location query.
		 */
		function set_location_query() {
			$this->slplus->database->reset_clauses();
			$this->slplus->database->order_by_array = array();

			/**
			 * Filter to filter out locations on the locations manager page.
			 *
			 * @filter slp_manage_location_where
			 *
			 * @params string  ''   current filters
			 */
			$this->extra_location_filters = apply_filters( 'slp_manage_location_where', '' );

			add_filter( 'slp_location_where', array( $this, 'set_location_filter' ) );
			add_action( 'slp_orderby_default', array( $this, 'set_location_order' ) );

			// Get the sort order and direction out of our URL
			//
			$this->cleanURL = preg_replace( '/&orderBy=\w*&sortorder=\w*/i', '', $_SERVER['REQUEST_URI'] );

			$dataQuery                   = $this->slplus->database->get_SQL( array( 'selectall', 'where_default' ) );
			$dataQuery                   = str_replace( '*', 'count(sl_id)', $dataQuery );
			$this->total_locations_shown = $this->slplus->db->get_var( $dataQuery );

			// Starting Location (Page)
			//
			// Search Filter, no actions, start from beginning
			//
			if ( isset( $_POST['searchfor'] ) && ! empty( $_POST['searchfor'] ) && ( $this->current_action === '' ) ) {
				$this->start = 0;

				// Set start to selected page..
				// Adjust start if past end of location count.
				//
			} else {
				$this->start =
					( isset( $_REQUEST['start'] ) && ctype_digit( $_REQUEST['start'] ) && ( (int) $_REQUEST['start'] >= 0 ) ) ?
						(int) $_REQUEST['start'] :
						0;

				if ( $this->start > ( $this->total_locations_shown - 1 ) ) {
					$this->start       = max( $this->total_locations_shown - 1, 0 );
					$this->hangoverURL = str_replace( '&start=', '&prevstart=', $this->hangoverURL );
				}
			}
		}

		/**
		 * Delete extended data records.
		 */
		function action_DeleteExtendedData() {
			$this->slplus->db->delete(
				$this->slplus->database->extension->plugintable['name'],
				array( 'sl_id' => $this->slplus->currentLocation->id )
			);
		}

		/**
		 * Save the extended data.
		 */
		function action_SaveExtendedData() {
			$action = isset( $_REQUEST['act'] ) ? $_REQUEST['act'] : '';

			// Check our extended column info and see if there is a matching property in exdata in currentLocation
			//
			$newValues = array();
			$this->set_active_columns();
			foreach ( $this->active_columns as $extraColumn ) {
				$slug = $extraColumn->slug;

				// Boolean force (off bools are not sent in request)
				//
				if ( $extraColumn->type === 'boolean' ) {
					$boolREQField                         = $slug . '-' . ( ( $action === 'add' ) ? '' : $this->slplus->currentLocation->id );
					$newValues[ $slug ]                   = empty( $_REQUEST[ $boolREQField ] ) ? 0 : 1;
					$this->slplus->currentLocation->$slug = $newValues[ $slug ];
				} else {
					$newValues[ $slug ] =
						isset( $this->slplus->currentLocation->exdata[ $slug ] ) ?
							$this->slplus->currentLocation->exdata[ $slug ] :
							'';
				}
			}

			// New values?  Write them to disk...
			//
			if ( count( $newValues ) > 0 ) {
				$this->slplus->database->extension->update_data( $this->slplus->currentLocation->id, $newValues );
			}
		}

		/**
		 * Locations / Add or Edit / Address
		 *
		 * @param string $section_name
		 */
		private function add_edit_address_group( $section_name ) {
		     // Locations / Add / Address
		     $group_name = __( 'Address' , 'store-locator-le' );

		     // Form Elements
		     //
		     $this->settings->add_ItemToGroup(array(
			     'type'         => 'details'  ,
			     'custom'       => $this->filter_EditLocationLeft_Address(),
			     'show_label'   => false,
			     'section'      => $section_name,
			     'group'        => $group_name
		        ));

		     // Submit
		     $this->settings->add_ItemToGroup(array(
			     'type'         => 'details'  ,
			     'custom'       => $this->filter_EditLocationLeft_Submit(''),
			     'show_label'   => false,
			     'section'      => $section_name,
			     'group'        => $group_name
		     ));

		     // Description
		     $this->settings->add_ItemToGroup(array(
			     'type'         => 'details'  ,
			     'custom'       => $this->get_address_description()  ,
			     'show_label'   => false,
			     'section'      => $section_name,
			     'group'        => $group_name
		     ));
		}

		/**
		 * Add extended data to location add/edit form.
		 */
		private function add_extended_data_to_location_edit( $section_name ) {
			$this->set_active_columns();

			/**
			 * Filter to set the active columns in the locations tab.
			 *
			 * @filter  slp_edit_location_change_extended_data_info
			 *
			 * @params  array   $this->active_columns
			 */
			$this->active_columns = apply_filters( 'slp_edit_location_change_extended_data_info' , $this->active_columns );
			if ( empty( $this->active_columns ) ) { return; }

			$data =
				((int)$this->slplus->currentLocation->id > 0)               ?
					$this->slplus->database->extension->get_data($this->slplus->currentLocation->id)   :
					null
			;

			// For each extended data field, add an item.
			//
			$groups = array();
			foreach( $this->active_columns as $col ) {
				$col->options = maybe_unserialize( $col->options );

				if ( empty( $col->options['addon'] ) ) {
					$group_name = __( 'Extended Data ' , 'store-locator-le' );

				} else {
					$group_name = $this->slplus->add_ons->instances[ $col->options['addon'] ]->name;
				}
				if ( !in_array( $group_name, $groups ) ) { $groups[] = $group_name; }

				$value      =
					is_null( $data )                  ?
						''                            :
						( isset( $data[$col->slug] )    ?
							$data[$col->slug]           :
							''
						)
					;

				$display_type='text';
				switch ($col->type) {
					case 'boolean':
						$display_type='checkbox';
						break;
					case 'text'      :
						$display_type='textarea';
						break;
				}

				$this->settings->add_ItemToGroup(array(
					'label'        => $col->label,
					'id'           => "edit-{$col->slug}",
					'name'          => "{$col->slug}-{$this->slplus->currentLocation->id}",
					'value'        => $value,
					'type'         => $display_type,
					'section'      => $section_name,
					'group'        => $group_name
					));
			}

			foreach ( $groups as $group_name ) {
				$this->settings->add_ItemToGroup(array(
					'type'         => 'details'  ,
					'custom'       => $this->filter_EditLocationLeft_Submit(''),
					'show_label'   => false,
					'section'      => $section_name,
					'group'        => $group_name
				));
			}
		}

	    /**
	     * Add the private class to locations marked private.
	     *
	     * @param $class the current class string for the manage locations location entry.
	     * @return string the modified CSS class with private attached if warranted.
	     */
	    function add_private_location_css( $class ) {
	        if ( $this->slplus->currentLocation->private ) {
	            $class .= ' private ';
	        }
	        return $class;
	    }

	    /**
	     * Show the private marker on the manage locations interface.
	     *
	     * @param $field_value
	     * @param $field
	     * @param $label
	     * @return mixed
	     */
	    function add_private_text_under_name( $field_value , $field , $label ) {
	        if ( $field === 'sl_store' ) {
	            if ( $this->slplus->currentLocation->private ) {
	                $field_value .=
	                    '<span class="privacy_please">' .
	                    __('private', 'store-locator-le') .
	                    '</span>';
	            }
	        }
	        return $field_value;
	    }

	     /**
	      * Returns the string that is the Location Info Form guts.
	      *
	      * @param boolean $addform - true if rendering add locations form
	      * @return string
	      */
	     public function create_settings_section_Add() {
		     // Edit Mode
		     //
		     if ( $this->current_action === 'edit' ) {
			     $panel_name        =  __('Edit','store-locator-le');
			     $this->slplus->currentLocation->set_PropertiesViaDB($_REQUEST['id']);
			     $panel_div_id      = 'edit_location';
			     $this->addingLocation          = false;

			     // Add Mode
			     //
		     } else {
			     $panel_name        =  __('Add','store-locator-le');
			     $panel_div_id      = 'add_location';
			     $this->addingLocation          = true;
		     }



	        // Add form
	        //
	        if ($this->addingLocation) {
	            $this->slplus->currentLocation->reset();
	            $address_description = __('Adding a new location.','store-locator-le');

	        // Setup current location based in incoming request data
	        //
	        } else {
		        $linked_post_message = '';
				if ( ! empty( $this->slplus->currentLocation->linked_postid ) ) {
					$linked_post_message =
						sprintf( __('Linked to %s page # %d' , 'store-locator-le' ) ,
							$this->slplus->add_ons->get_product_url( 'slp-pages' ),
							$this->slplus->currentLocation->linked_postid
						);
				}

		        $lat_long_message = '';
	            if ( is_numeric($this->slplus->currentLocation->latitude) && is_numeric($this->slplus->currentLocation->longitude) ) {
	                $lat_long_message =
		                sprintf(
			                __('Original latitude %f , longitude %f.', 'store-locator-le' ),
			                $this->slplus->currentLocation->latitude,
		                    $this->slplus->currentLocation->longitude
			                );
	            }

		        $address_description =
			        sprintf(
				        __( 'Editing location #%d %s %s' , 'store-locator-le' ),
				        $this->slplus->currentLocation->id ,
				        $linked_post_message ,
				        $lat_long_message
			        );

	        }

		     $address_description .= '<br/>' .
			     sprintf('<a href="%s" target="csa" alt="%s" title="%s">%s</a>',
				     'http://www.latlong.net',
				     __('Latitude/Longitude lookup.','store-locator-le') ,
				     __('Latitude/Longitude lookup.','store-locator-le') ,
				     __('The LatLong.net website can help you locate an exact latitude/longitude.','store-locator-le')
			     )
			     ;


		     // Add Location Section
		     //
		     $this->settings->add_section(array(
			     'name'          => $panel_name,
			     'div_id'        => $panel_div_id,
			     'opening_html' =>
				     "<form id='manualAddForm' name='manualAddForm' method='post'>" .
				     ($this->addingLocation?'<input type="hidden" id="act" name="act" value="add" />':'<input type="hidden" id="act" name="act" value="edit" />') .
				     "<input type='hidden' name='id' "                                                            .
				     "id='id' value='{$this->slplus->currentLocation->id}' />"                               .
				     "<input type='hidden' name='locationID' "                                                    .
				     "id='locationID' value='{$this->slplus->currentLocation->id}' />"                       .
				     "<input type='hidden' name='linked_postid-{$this->slplus->currentLocation->id}' "            .
				     "id='linked_postid-{$this->slplus->currentLocation->id}' value='"                       .
				     $this->slplus->currentLocation->linked_postid                                           .
				     "' />"                                                                                  .
				     ( isset($_REQUEST['start'])  ? "<input type='hidden' name='start' id='start' value='{$_REQUEST['start']}' />" : '' ) .
				     "<a name='a{$this->slplus->currentLocation->id}'></a>"
		            ,
			     'closing_html' =>
			        '</form>'
		          ));

		     // Locations / Add / Address
		     $this->add_edit_address_group( $panel_name );

		     // Location / Add / Details
		     //
		     add_filter('slp_edit_location_right_column' ,array($this,'filter_EditLocationRight_Address')  , 5);
		     add_filter('slp_edit_location_right_column' ,array($this,'filter_EditLocationLeft_Submit')    ,99);

		     /**
		      * Filter to add HTML to the top of the add/edit location form.
		      *
		      * @filter     slp_edit_location_right_column
		      *
		      * @params     string      current HTML
		      */

		     $group_name = __( 'Details' , 'store-locator-le' );
		     $this->settings->add_ItemToGroup(array(
			     'type'         => 'details'  ,
			     'custom'       => apply_filters('slp_edit_location_right_column','')   ,
			     'show_label'   => false,
			     'section'      => $panel_name,
			     'group'        => $group_name
		     ));


		     // Location / Add / Extended
		     //
		     $this->add_extended_data_to_location_edit( $panel_name );
	     }

	    //-------------------------------------
	    // createstring Methods
	    //-------------------------------------

	    /**
	     * Create HTML string for hidden inputs we need to keep track of filters, etc.
	     *
	     * @return string $HTML
	     */
	    function createstring_HiddenInputs() {
	        $html = '';
	        $onlyHide = array('start');
	        foreach($_REQUEST as $key=>$val) {
	            if (!in_array($key,$onlyHide,true)) { continue; }
	            $html.="<input type='hidden' value='$val' id='$key' name='$key'>\n";
	        }
	        return $html;
	    }

	    /**
	     * Create the add/edit form field.
	     *
	     * Leave fldLabel blank to eliminate the leading <label>
	     *
	     * inType can be 'input' (default) or 'textarea'
	     *
	     * @param string $fldName name of the field, base name only
	     * @param string $fldLabel label to show ahead of the input
	     * @param string $fldValue
	     * @param string $inputclass class for input field
	     * @param boolean $noBR skip the <br/> after input
	     * @param string $inType type of input field (default:input)
	     * @param string $placeholder the placeholder for the input field (default: blank)
	     * @return string the form HTML output
	     */
	    function createstring_InputElement($fldName,$fldLabel,$fldValue, $inputClass='', $noBR = false, $inType='input', $placeholder = '') {
	        $matches = array();
	        $matchStr = '/(.+)\[(.*)\]/';
	        if (preg_match($matchStr,$fldName,$matches)) {
	            $fldName = $matches[1];
	            $subFldName = '['.$matches[2].']';
	        } else {
	            $subFldName='';
	        }
	        return
	            (empty($fldLabel)?'':"<label  for='{$fldName}-{$this->slplus->currentLocation->id}{$subFldName}'>{$fldLabel}</label>").
	            "<{$inType} "                                                                   .
	                "id='edit-{$fldName}-{$this->slplus->currentLocation->id}{$subFldName}' "   .
	                "data-field='{$fldName}' ".
	                "name='{$fldName}-{$this->slplus->currentLocation->id}{$subFldName}' "      .
	                ( empty ( $placeholder ) ? '' : " placeholder='{$placeholder}' " )          .
	                (($inType==='input')?
	                        "value='".esc_html($fldValue)."' "  :
	                        "rows='5' cols='17'  "
	                 )                                                          .
	                (empty($inputClass)?'':"class='{$inputClass}' ")            .
	            '>'                                                             .
	            (($inType==='textarea')?esc_textarea($fldValue):'')             .
	            (($inType==='textarea')?'</textarea>'   :'')                    .
	            ($noBR?'':'<br/>')
	            ;
	    }

	    /**
	     * Adds the extended data columns.
	     *
	     * @param array $current_cols   The current columns
	     *
	     * @return array
	     */
	    function add_extended_data_to_manage_locations_table($current_cols) {
		    $this->set_active_columns();
		    if ( empty( $this->active_columns ) ) { return $current_cols; }

		    foreach( $this->active_columns as $col ) {
	            $current_cols[$col->slug] = $col->label;
	        }

	        return $current_cols;
	    }

	    /**
	     * Render the extra fields on the manage location table.
	     *
	     * SLP Filter: slp_column_data
	     *
	     * @param string $theData  - the option_value field data from the database
	     * @param string $theField - the name of the field from the database (should be sl_option_value)
	     * @param string $theLabel - the column label for this column (should be 'Categories')
	     * @return type
	     */
	    function filter_AddImageToManageLocations($theData,$theField,$theLabel) {
	        if (
	            ($theField === 'sl_image') &&
	            ($theLabel === __('Image'        ,'store-locator-le')
	            )
	        ) {
	            $theData =
	                ( $this->slplus->currentLocation->image != '' )
	                    ?
	                    "<a href='{$this->slplus->currentLocation->image}' target='blank'>" .
	                    sprintf('<img src="%s" alt="%s" title="%s" class="location_image manage_locations" />',
	                        $this->slplus->currentLocation->image ,
	                        $this->slplus->currentLocation->store . __(' image ' , 'store-locator-le'),
	                        $this->slplus->currentLocation->store . __(' image ' , 'store-locator-le')
	                    ) .
	                    '</a>'
	                    :
	                    '' ;
	        }
	        return $theData;
	    }

	    /**
	     * Add the lat/long under the store name.
	     *
	     * @param string $field_value
	     * @param type $field
	     * @param type $label
	     * @return type
	     */
	    function filter_AddLatLongUnderName( $field_value, $field, $label) {
	        if ($field === 'sl_store') {
	            $commaOrSpace = ($this->slplus->currentLocation->latitude . $this->slplus->currentLocation->longitude!=='')? ',':' ';

	            if ($commaOrSpace != ' ') {
	                $latlong_url =
	                    sprintf('https://%s?saddr=%f,%f',
	                        $this->slplus->options['map_domain'],
	                        $this->slplus->currentLocation->latitude,
	                        $this->slplus->currentLocation->longitude
	                        );
	                $latlong_text =
	                    sprintf('<a href="%s" target="csa_map">@ %f %s %f</a></span>',
	                        $latlong_url,
	                        $this->slplus->currentLocation->latitude,
	                        $commaOrSpace,
	                        $this->slplus->currentLocation->longitude
	                        );
	            } else {
	                $latlong_text = __('Inactive. Geocode to activate.','store-locator-le');
	            }

	            $field_value =
	                sprintf('<span class="store_name">%s</span>'.
	                        '<span class="store_latlong">%s</span>',
	                        $field_value,
	                        $latlong_text
	                        );
	        }
	        return $field_value;
	    }


	    /**
	     * Add the left column to the add/edit locations form.
	     *
	     * @return string HTML of the form inputs
	     */
	    function filter_EditLocationLeft_Address( ) {
	        $HTML =
	            $this->createstring_InputElement(
	                'store',
	                __('Name', 'store-locator-le'),
	                $this->slplus->currentLocation->store
	                ).
	            $this->createstring_InputElement(
	                'address',
	                __('Street - Line 1', 'store-locator-le'),
	                $this->slplus->currentLocation->address
	                ).
	            $this->createstring_InputElement(
	                'address2',
	                __('Street - Line 2', 'store-locator-le'),
	                $this->slplus->currentLocation->address2
	                ).
	            $this->createstring_InputElement(
	                'city',
	                __('City', 'store-locator-le'),
	                $this->slplus->currentLocation->city
	                ).
	            $this->createstring_InputElement(
	                'state',
		            __('State', 'store-locator-le'),
	                $this->slplus->currentLocation->state
	                ).
	            $this->createstring_InputElement(
	                'zip',
		            __('ZIP / Postal Code', 'store-locator-le'),
	                $this->slplus->currentLocation->zip
	                ).
	            $this->createstring_InputElement(
	                'country',
	                __('Country','store-locator-le'),
	                $this->slplus->currentLocation->country
	                ).
	            $this->createstring_InputElement(
	                    'latitude',
	                    __('Latitude (N/S)', 'store-locator-le'),
	                    $this->slplus->currentLocation->latitude,
	                    '', false, 'input',
	                    __('Leave blank to have Google look up the latitude.', 'store-locator-le')
	                    ).
	            $this->createstring_InputElement(
	                    'longitude',
	                    __('Longitude (E/W)', 'store-locator-le'),
	                    $this->slplus->currentLocation->longitude,
	                    '', false, 'input',
	                    __('Leave blank to have Google look up the longitude.', 'store-locator-le')
	                    );

	        $HTML .= $this->slplus->helper->CreateCheckboxDiv(
	            "private-{$this->slplus->currentLocation->id}" ,
	            __('Private Entry' , 'store-locator-le') ,
	            __('If checked the listing will not show up in location search results.' , 'store-locator-le' ),
	            '' ,
	            false ,
	            0,
	            $this->slplus->is_CheckTrue( $this->slplus->currentLocation->private )
	        );

	        return $HTML;
	    }

	    /**
	     * Put the add/cancel button on the add/edit locations form.
	     *
	     * This is rendered AFTER other HTML stuff.
	     *
	     * @param string $HTML the html of the base form.
	     * @return string HTML of the form inputs
	     */
	    function filter_EditLocationLeft_Submit($HTML) {
	        $edCancelURL = isset( $_REQUEST['id'] ) ?
	            preg_replace('/&id='.$_REQUEST['id'].'/', '',$_SERVER['REQUEST_URI']) :
	            $_SERVER['REQUEST_URI']
	            ;
	        $alTitle =
	            ($this->addingLocation?
	                __('Add Location','store-locator-le'):
	                sprintf("%s #%d",__('Update Location', 'store-locator-le'),$this->slplus->currentLocation->id)
	            );

	        $value   =
	                ($this->addingLocation)    ?
	                __('Add'   ,'store-locator-le')  :
	                __('Update','store-locator-le')  ;

	        $onClick =
	                ($this->addingLocation)                                       ?
	                "AdminUI.doAction('add' ,'','manualAddForm');"    :
	                "AdminUI.doAction('save','','locationForm' );"    ;

	        return
	            $HTML .
	            "<div id='slp_form_buttons'>"                                                           .
	            "<input "                                                                               .
	                "type='submit'        "                                                             .
	                'value="'  .$value  .'" '                                                           .
	                'onClick="'.$onClick.'" '                                                           .
	                "' alt='$alTitle' title='$alTitle' class='button-primary'"                          .
	                ">"                                                                                 .
	            "<input type='button' class='button' "                                                  .
	                "value='".__('Cancel', 'store-locator-le')."' "                                           .
	                "onclick='location.href=\"".$edCancelURL."\"'>"                                     .
	            "<input type='hidden' name='option_value-{$this->slplus->currentLocation->id}' "        .
	                "value='".($this->addingLocation?'':$this->slplus->currentLocation->option_value)   .
	                "' />"                                                                              .
	            "</div>"
	            ;
	    }

	    /**
	     * Add the right column to the add/edit locations form.
	     *
	     * @param string $HTML the html of the base form.
	     * @return string HTML of the form inputs
	     */
	    function filter_EditLocationRight_Address($HTML) {
	        return
	            $this->createstring_InputElement(
	                    'description',
	                    __('Description', 'store-locator-le'),
	                    $this->slplus->currentLocation->description,
	                    '',
	                    false,
	                    'textarea'
	                    ).
	            $this->createstring_InputElement(
	                    'url',
	                    $this->slplus->WPML->get_text('label_website'),
	                    $this->slplus->currentLocation->url
	                    ).
	            $this->createstring_InputElement(
		                'email',
		                $this->slplus->WPML->get_text( 'label_email'     ),
	                    $this->slplus->currentLocation->email
	                    ).
	            $this->createstring_InputElement(
	                    'hours',
	                    $this->slplus->WPML->get_text('label_hours'),
	                    $this->slplus->currentLocation->hours,
	                    '',
	                    false,
	                    'textarea'
	                    ).
	            $this->createstring_InputElement(
	                    'phone',
	                    $this->slplus->WPML->get_text('label_phone'),
	                    $this->slplus->currentLocation->phone
	                    ).
	            $this->createstring_InputElement(
	                    'fax',
	                    $this->slplus->WPML->get_text('label_fax'),
	                    $this->slplus->currentLocation->fax
	                    ).
	            $this->createstring_InputElement(
	                    'image',
	                    __('Image URL', 'store-locator-le'),
	                    $this->slplus->currentLocation->image
	                    ) .
	            $HTML
	            ;
	    }

	    /**
	     * Set the invalid highlighting class.
	     *
	     * @param string $class
	     * @return string the new class name for invalid rows
	     */
	    function filter_InvalidHighlight($class) {

	        if (($this->slplus->currentLocation->latitude == '') ||
	            ($this->slplus->currentLocation->longitude == '')
	            ) {
	            $class .= ' invalid ';
	        }

		    /**
		     * Filter to add classes to the manage locations class for invalid location entries.
		     *
		     * @filter      slp_invalid_highlight
		     *
		     * @params      string  $class  existing class names
		     */
		    return apply_filters('slp_invalid_highlight',$class);
	    }

		/**
		 * Get the extended columns meta data and remember them within this class.
		 */
		private function set_active_columns() {
			if (!isset($this->active_columns)) {
				$this->active_columns = $this->slplus->database->extension->get_active_cols();
			}
		}

	    // Add a locations
	    //
	    function location_Add() {

	        //Inserting addresses by manual input
	        //
	        $locationData = array();
	        if ( isset($_POST['store-']) && !empty($_POST['store-'])) {
	            foreach ($_POST as $key=>$sl_value) {
	                if (preg_match('#\-$#', $key)) {
	                    $fieldName='sl_'.preg_replace('#\-$#','',$key);
	                    $locationData[$fieldName]=(!empty($sl_value)?$sl_value:'');
	                }
	            }

	            $skipGeocode =
	                ( $this->current_action === 'add'     ) &&
	                ( isset($locationData['sl_latitude'  ]) && is_numeric($locationData['sl_latitude'  ])    ) &&
	                ( isset($locationData['sl_longitude' ]) && is_numeric($locationData['sl_longitude' ])    )
	                ;
	            $response_code =
	                $this->location_AddToDatabase(
	                    $locationData,
	                    'none',
	                    $skipGeocode
	                    );

	            print "<div class='updated fade'>".
	                    stripslashes_deep($_POST['store-']) . ' ' .
	                    __('added successfully','store-locator-le') . ' ' .
	                    $response_code .
	                '.</div>'
	                ;

	        } else {
	            print "<div class='updated fade'>".
	                    __('Location not added.','store-locator-le') . ' ' .
	                    __('The add location form on your server is not rendering properly.','store-locator-le') .
	                    '</div>';
	        }
	    }

	    /**
	     * Add an address into the SLP locations database.
	     *
	     * Moved into location class.
	     *
	     * @param array[] $locationData
	     * @param string $duplicates_handling
	     * @param boolean $skipGeocode
	     * @return string
	     *
	     */
	    function location_AddToDatabase($locationData,$duplicates_handling='none',$skipGeocode=false) {
	        return $this->slplus->currentLocation->add_to_database( $locationData , $duplicates_handling , $skipGeocode );
	    }

	    /**
	     * Save a location.
	     */
	    function location_save() {
	        if ( ! $this->slplus->currentLocation->isvalid_ID( null, 'locationID' ) ) { return; }
	        $this->slplus->notifications->delete_all_notices();

	        // Get our original address first
	        //
	        $this->slplus->currentLocation->set_PropertiesViaDB($_REQUEST['locationID']);
	        $priorIsGeocoded=
	            is_numeric($this->slplus->currentLocation->latitude) &&
	            is_numeric($this->slplus->currentLocation->longitude)
	            ;
	        $priorAddress   =
	                $this->slplus->currentLocation->address . ' '  .
	                $this->slplus->currentLocation->address2. ', ' .
	                $this->slplus->currentLocation->city    . ', ' .
	                $this->slplus->currentLocation->state   . ' '  .
	                $this->slplus->currentLocation->zip
	                ;

	        // Update The Location Data
	        //
	        foreach ($_POST as $key=>$value) {
	            if (preg_match('#\-'.$this->slplus->currentLocation->id.'#', $key)) {
	                $slpFieldName = preg_replace('#\-'.$this->slplus->currentLocation->id.'#', '', $key);
	                if (($slpFieldName === 'latitude') || ($slpFieldName === 'longitude')) {
	                    if (!is_numeric($value)) { continue; }
	                }

	                // Has the data changed?
	                //
	                $stripped_value = stripslashes_deep($value);
	                if ($this->slplus->currentLocation->$slpFieldName !== $stripped_value) {
	                    $this->slplus->currentLocation->$slpFieldName = $stripped_value;
	                    $this->slplus->currentLocation->dataChanged = true;
	                }
	            }
	        }

	        // Missing Checkboxes (private)
	        //
	        if ( $this->slplus->currentLocation->private && ! isset( $_POST["private-{$this->slplus->currentLocation->id}"] ) ) {
	            $this->slplus->currentLocation->private = false;
	            $this->slplus->currentLocation->dataChanged = true;
	        }

	        // RE-geocode if the address changed
	        // or if the lat/long is not set
	        //
	        $newAddress   =
	                $this->slplus->currentLocation->address . ' '  .
	                $this->slplus->currentLocation->address2. ', ' .
	                $this->slplus->currentLocation->city    . ', ' .
	                $this->slplus->currentLocation->state   . ' '  .
	                $this->slplus->currentLocation->zip
	                ;
	        if (   ($newAddress!=$priorAddress) || !$priorIsGeocoded) {
	            $this->slplus->currentLocation->do_geocoding($newAddress);
	        }


		    /**
		     * Hook executes when a location save action is called from manage locations.
		     *
		     * @action slp_location_save
		     */
		    do_action('slp_location_save');
	        if ($this->slplus->currentLocation->dataChanged) {
	            $this->slplus->currentLocation->MakePersistent();
	        }

		    /**
		     * Hook executes after a location has been saved from the manage locations interface.
		     *
		     * @action slp_location_saved
		     *
		     */
	        do_action('slp_location_saved');

	        // Show Notices
	        //
	        $this->slplus->notifications->display();
	    }

	    /**
	     * Build the action buttons HTML string on the first column of the manage locations panel.
	     *
	     * Applies the slp_manage_locations_actionbuttons filter.
	     *
	     * @return string
	     */
	    function createstring_ActionButtons() {
	        $buttons_HTML =
	            sprintf(
	                '<a class="dashicons dashicons-welcome-write-blog slp-no-box-shadow" alt="%s" title="%s" href="%s" onclick="%s"></a>'   ,
	                __('edit','store-locator-le')                                                 ,
	                __('edit','store-locator-le')                                                 ,
	                '#'                                                                     ,
	                "jQuery('#id').val('{$this->slplus->currentLocation->id}');".
	                "AdminUI.doAction('edit','');"
	            ).
	            sprintf(
	                '<a class="dashicons dashicons-trash slp-no-box-shadow" alt="%s" title="%s" href="%s" onclick="%s"></a>'   ,
	                __('delete','store-locator-le')                                                              ,
	                __('delete','store-locator-le')                                                              ,
	                '#'                                                                                    ,
	                "jQuery('#id').val('{$this->slplus->currentLocation->id}');".
	                "AdminUI.doAction('delete','".sprintf(__('Delete %s?','store-locator-le'), esc_js($this->slplus->currentLocation->store) )."'); "
	            )
	            ;

		    /**
		     * Filter to Build the action buttons HTML string on the first column of the manage locations panel.
		     *
		     * @filter      slp_manage_locations_actionbuttons
		     *
		     * @params  string  current HTML
		     * @params  array   current location data
		     */
		    return apply_filters('slp_manage_locations_actionbuttons',$buttons_HTML, (array) $this->slplus->currentLocation->locationData);

	    }

	    /**
	     * Create the bulk actions drop down for the top-of-table navigation.
	     *
	     */
	    function createstring_BulkActionsBlock() {

	        // Setup the properties array for our drop down.
	        //
	        $dropdownItems = array(
	                array(
	                    'label'     =>  __('Bulk Actions','store-locator-le') ,
	                    'value'     => '-1'                             ,
	                    'selected'  => true
	                ),
	                array(
	                    'label'     =>  __('Delete Permanently','store-locator-le')   ,
	                    'value'     => 'delete'                                 ,
	                )
	            );

		    /**
		     * Filter to add a menu entry to the bulk actions drop down on the locations manager.
		     *
		     * @filter slp_locations_manage_bulkactions
		     *
		     * @params  array   $dropdownitems
		     */
		    $dropdownItems = apply_filters('slp_locations_manage_bulkactions',$dropdownItems);

	        // Loop through the action boxes content array
	        //
	        $baExtras = '';
	        foreach ($dropdownItems as $item) {
	            if (isset($item['extras']) && !empty($item['extras'])) {
	                $baExtras .= $item['extras'];
	            }
	        }

	        // Create the box div string.
	        //
	        $morebox = "'#extra_'+jQuery('#actionType').val()"        ;
	        $filter_dialog_title=__('Options','store-locator-le');
	        $dialog_options =
	            "appendTo: '#locationForm'      , " .
	            "minHeight: 50                  , " .
	            "minWidth: 450                  , " .
	            "title: jQuery('#actionType option:selected').text()+' $filter_dialog_title'  , " .
	            "position: { my: 'left top', at: 'left bottom', of: '#actionType' } "
	            ;

	        // Action confirmation.
	        //
	        $confirmPretext = __('Are you sure you want to ','store-locator-le');


	        return
	            $this->slplus->helper->createstring_DropDownMenuWithButton(
	                array(
	                        'id'            => 'actionType'             ,
	                        'name'          => 'action'                 ,
	                        'items'         => $dropdownItems           ,
	                        'onchange'      => "jQuery({$morebox}).dialog({ $dialog_options });",
	                        'buttonlabel'   => __('Apply','store-locator-le') ,
	                        'onclick'       =>
	                            'AdminUI.doAction(jQuery(\'#actionType\').val(),\''.
	                                $confirmPretext .
	                                '\'+jQuery(\'#actionType option:selected\').text()+\'?\');'
	                    )
	                ).
	                $baExtras
	            ;
	    }

	    /**
	     * Create the fitlers drop down for the top-of-table navigation.
	     *
	     */
	    function createstring_FiltersBlock() {

	        // Setup the properties array for our drop down.
	        //
	        $dropdownItems = array(
	                array(
	                    'label'     =>  __('Show All','store-locator-le') ,
	                    'value'     => 'show_all'                   ,
	                    'selected'  => true
	                ),
	            );

		    /**
		     * Filter to add entries to the filters drop down menu on the locations manager.
		     *
		     * @filter slp_locations_manage_filters
		     *
		     * @params
		     */
		    $dropdownItems = apply_filters('slp_locations_manage_filters',$dropdownItems);

	        // Loop through the action boxes content array
	        //
	        $baExtras = '';
	        foreach ($dropdownItems as $item) {
	            if (isset($item['extras']) && !empty($item['extras'])) {
	                $baExtras .= $item['extras'];
	            }
	        }

	        // Create the box div string.
	        //
	        $morebox = "'#extra_'+jQuery('#filterType').val()"        ;
	        $filter_dialog_title = __('Filter Locations By','store-locator-le');
	        $dialog_options =
	            "appendTo: '#locationForm'      , " .
	            "minWidth: 450                  , " .
	            "title: '$filter_dialog_title'  , " .
	            "position: { my: 'left top', at: 'left bottom', of: '#filterType' } "
	            ;


	        return
	            $this->slplus->helper->createstring_DropDownMenuWithButton(
	                array(
	                        'id'            => 'filterType'             ,
	                        'name'          => 'filter'                 ,
	                        'items'         => $dropdownItems           ,
	                        'onchange'      => "jQuery({$morebox}).dialog({ $dialog_options });" ,
	                        'buttonlabel'   => __('Filter','store-locator-le') ,
	                        'onclick'       => 'AdminUI.doAction(jQuery(\'#filterType\').val(),\'\');'
	                    )
	                ).
	                $baExtras
	            ;
	    }

	    /**
	     * Create the display drop down for the top-of-table navigation.
	     */
	    private function createstring_DisplayBlock() {
	        $dropdownItems = array();
		    $selections = array( '10', '100' , '500' , '1000' , '5000' , '10000' );

		    foreach  ( $selections as $selection ) {
				$dropdownItems[] =
			        array(
				        'label'     =>  sprintf(__('%d locations','store-locator-le'), $selection )   ,
				        'value'     => $selection ,
				        'selected'  => ($this->slplus->options_nojs['admin_locations_per_page'] === $selection)
			        );
		    }

		    /**
		     * Filter to add menu items to the display drop down on the manage locations table.
		     *
		     * @filter  slp_locations_manage_display
		     *
		     * @params  array   $dropdownItems
		     */

		    return
	            $this->slplus->helper->createstring_DropDownMenuWithButton(
	                array(
	                        'id'            => 'admin_locations_per_page'                ,
	                        'name'          => 'admin_locations_per_page'                    ,
	                        'items'         => apply_filters('slp_locations_manage_display',$dropdownItems)               ,
	                        'buttonlabel'   => __('Display','store-locator-le')   ,
	                        'onclick'       => 'AdminUI.doAction(\'locationsperpage\',\'\',\'locationForm\',\'act\');'
	                    )
	                )
	            ;
	    }

	    /**
	     * Create the manage locations pagination block
	     *
	     * @param int $totalLocations
	     * @param int $num_per_page
	     * @param int $start
	     * @return string
	     */
	    private function createstring_PaginationBlock($totalLocations = 0, $num_per_page = 10, $start = 0) {

	        // Variable Init
	        $pos=0;
	        $prev = min(max(0,$start-$num_per_page),$totalLocations);
	        $next = min(max(0,$start+$num_per_page),$totalLocations);
	        $num_per_page = max(1,$num_per_page);
	        $qry = isset($_GET['q'])?$_GET['q']:'';
	        $cleared=preg_replace('/q=$qry/', '', $this->hangoverURL);

	        $extra_text=(trim($qry)!='')    ?
	            __("for your search of", 'store-locator-le').
	                " <strong>\"$qry\"</strong>&nbsp;|&nbsp;<a href='$cleared'>".
	                __("Clear&nbsp;Results", 'store-locator-le')."</a>" :
	            "" ;

	        // URL Regex Replace
	        //
	        if (preg_match('#&start='.$start.'#',$this->hangoverURL)) {
	            $prev_page=str_replace("&start=$start","&start=$prev",$this->hangoverURL);
	            $next_page=str_replace("&start=$start","&start=$next",$this->hangoverURL);
	        } else {
	            $prev_page=$this->hangoverURL ."&start=$prev";
	            $next_page=$this->hangoverURL."&start=$next";
	        }

	        // Pages String
	        //
	        $pagesString = '';
		    $this->script_data['all_displayed']	= ( $totalLocations <= $num_per_page );
	        if ( ! $this->script_data['all_displayed'] ) {
	            if ((($start/$num_per_page)+1)-5<1) {
	                $beginning_link=1;
	            } else {
	                $beginning_link=(($start/$num_per_page)+1)-5;
	            }
	            if ((($start/$num_per_page)+1)+5>(($totalLocations/$num_per_page)+1)) {
	                $end_link=(($totalLocations/$num_per_page)+1);
	            } else {
	                $end_link=(($start/$num_per_page)+1)+5;
	            }
	            $pos=($beginning_link-1)*$num_per_page;
	            for ($k=$beginning_link; $k<$end_link; $k++) {
	                if (preg_match('#&start='.$start.'#',$_SERVER['QUERY_STRING'])) {
	                    $curr_page=str_replace("&start=$start","&start=$pos",$_SERVER['QUERY_STRING']);
	                }
	                else {
	                    $curr_page=$_SERVER['QUERY_STRING']."&start=$pos";
	                }
	                if (($start-($k-1)*$num_per_page)<0 || ($start-($k-1)*$num_per_page)>=$num_per_page) {
	                    $pagesString .= "<a class='page-button' href=\"{$this->hangoverURL}&$curr_page\" >";
	                } else {
	                    $pagesString .= "<a class='page-button thispage' href='#'>";
	                }


	                $pagesString .= "$k</a>";
	                $pos=$pos+$num_per_page;
	            }
	        }

	        $prevpages =
	            "<a class='prev-page page-button" .
	                ((($start-$num_per_page)>=0) ? '' : ' disabled' ) .
	                "' href='".
	                ((($start-$num_per_page)>=0) ? $prev_page : '#' ).
	                "'>‹</a>"
	            ;
	        $nextpages =
	            "<a class='next-page page-button" .
	                ((($start+$num_per_page)<$totalLocations) ? '' : ' disabled') .
	                "' href='".
	                ((($start+$num_per_page)<$totalLocations) ? $next_page : '#').
	                "'>›</a>"
	            ;

	        $pagesString =
	            $prevpages .
	            $pagesString .
	            $nextpages
	            ;

	        return
	                '<div id="slp_pagination_pages" class="tablenav-pages">'    .
	                    '<span class="displaying-num">'                         .
	                            "<span id='total_locations'>{$totalLocations}</span>" .
	                            ' '.__('locations','store-locator-le')                .
	                        '</span>'                                           .
	                        '<span class="pagination-links">'                   .
	                        $pagesString                                        .
	                        '</span>'                                           .
	                    '</div>'                                                .
	                    $extra_text
	            ;
	    }

	    /**
	     * Attach the HTML for the manage locations panel to the settings object as a new section.
	     *
	     * This will be rendered via the render_adminpage method via the standard wpCSL Settings object display method.
	     */
	    public function create_settings_section_Manage() {
	        $this->settings->add_section(
	            array(
	                    'name'          => __('Manage','store-locator-le'),
	                    'div_id'        => 'current_locations',
	                    'description'   => $this->create_string_manage_locations_table_and_header(),
	                    'auto'          => true,
	                    'innerdiv'      => true
	                )
	         );
	    }

	    /**
	     * Build the HTML string for the locations table.
	     */
	    private function create_string_manage_locations_table_and_header() {
	        $this->set_location_query();

		    // No locations exist.
		    //
		    if ( ( $this->total_locations_shown < 1 ) && ! $this->slplus->database->had_where_clause ) {
				return  $this->slplus->helper->create_string_wp_setting_error_box( __("No locations have been created yet.", 'store-locator-le') );
		    }

		    // We have locations, show them.
		    //
	        return
		        wp_nonce_field( 'screen-options-nonce', 'screenoptionnonce', false ) .
	            $this->createstring_HiddenInputs()              .
	            $this->createstring_PanelManageTableTopActions().
                '<div class="manage_locations_table_outside">'    .
	            $this->create_string_manage_locations_table() .
                '</div>' .
	            '<div class="tablenav bottom">'                 .
	                $this->createstring_PanelManageTablePagination().
	            '</div>'
	            ;
	    }

	    /**
	     * Build the content of the manage locations table.
	     *
	     * TODO: convert this to a proper WP_List_Table query and items configuration.
	     *
	     * @return string
	     */
	    private function create_string_manage_locations_table() {
		    if ( $this->total_locations_shown < 1 ) {
			    return $this->slplus->helper->create_string_wp_setting_error_box( __("Location search or filter returned no matches.", 'store-locator-le')  );
		    }

	        // Formatting
	        //
	        $html = '';
	        $colorClass = '';
	        $this->set_manage_locations_formatting();
	        $this->get_columns();

	        // Setup Data Query
	        //
		    $this->slplus->database->reset_clauses();
		    $this->slplus->database->order_by_array = array();
	        $sqlCommand = array( 'selectall' , 'where_default' , 'orderby_default' , 'limit_one' , 'manual_offset' );

	        // Start at the desired starting position in the list (for secondary pages)
	        //
	        $offset = $this->start;
	        $sqlParams = array( $offset );

		    // Tell the WP Engine to get one record at a time
	        // Until we reached how many we want per page.
	        //
		    $this->empty_columns = $this->columns;
	        while (
	            ( ($offset - $this->start) < $this->slplus->options_nojs['admin_locations_per_page'] )   &&
	            ( $location = $this->slplus->database->get_Record( $sqlCommand , $sqlParams , 0 ) )
	        ) {

	            // Set current location
	            //
	            $this->slplus->currentLocation->set_PropertiesViaArray($location);

	            // Row color
	            //
	            $colorClass = (($colorClass==='alternate')?'':'alternate');

		        /**
		         * Filter to add manage locations css classes.
		         *
		         * @filter  slp_locations_manage_cssclass
		         *
		         * @params
		         */

		        $extraCSSClasses = apply_filters('slp_locations_manage_cssclass','');

	            // Clean Up Data with trim()
	            //
	            $location=array_map("trim",$location);

	            // Custom Filters to set the links on special data like URLs and Email
	            //
	            $location['sl_url']= esc_url( $location['sl_url'] );

	            $location['sl_url']=($location['sl_url']!="")?
	                "<a href='{$location['sl_url']}' target='blank' ".
	                "alt='{$location['sl_url']}' title='{$location['sl_url']}'>".
	                $this->slplus->WPML->get_text('label_website') .
	                '</a>' :
	                '';

	            $location['sl_email'] =
	                ! empty( $location['sl_email'] )        ?
	                    sprintf('<a href="mailto:%s" target="blank" alt="%s" title="%s">%s</a>' ,
	                        $location['sl_email'],
	                        $location['sl_email'],
	                        $location['sl_email'],
	                        $this->slplus->WPML->get_text('label_email')
	                    )                           :
	                    ''                                  ;

	            $location['sl_description']=($location['sl_description']!="")?
	                "<a onclick='alert(\"".esc_js($location['sl_description'])."\")' href='#'>".
	                __("View", 'store-locator-le')."</a>" :
	                "" ;

	            $cleanName = urlencode($this->slplus->currentLocation->store);

	            // Location Row Start
	            //
	            $html .=
	                "<tr "                                                                              .
	                "data-id='{$this->slplus->currentLocation->id}' "                                   .
	                "data-type='base' "                                                                 .
	                "id='location-{$this->slplus->currentLocation->id}' "                               .
	                "name='{$cleanName}' "                                                              .
	                "class='slp_managelocations_row $colorClass $extraCSSClasses' "                     .
	                ">"                                                                                 .
	                "<td class='th_checkbox slp_th slp_checkbox'>"                                      .
	                "<input type='checkbox' class='slp_checkbox' name='sl_id[]' value='{$this->slplus->currentLocation->id}'>"        .
	                '</td>'                                                                             .
	                "<td><div class='action_buttons'>"                                                  .
	                $this->createstring_ActionButtons()                                                 .
	                "</div></td>"
	            ;

	            // Data Columns
	            //
		        list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();
	            foreach ($columns as $column_key => $column_display_name) {
		            if ( $column_key === 'sl_id' ) { continue; }

		            $class = array( 'slp_manage_locations_cell');
		            $class[] = sanitize_title($column_display_name);
		            if ( in_array( $column_key, $hidden ) ) {
			            $class[] = 'hidden';
		            }

	                if ( ! isset( $location[$column_key] ) ) { $location[$column_key] = ''; }


		            /**
		             * Filter to
		             *
		             * @filter  slp_column_data
		             *
		             * @params  string    column key without slashes
		             * @params  string    column key
		             * @params  string    column display name
		             */

		            $column_data = apply_filters('slp_column_data',stripslashes($location[$column_key]), $column_key, $column_display_name);

		            if ( ! empty( $column_data ) ) {
			            unset( $this->empty_columns[$column_key] );
		            }


		            $class      = "class='" . join( ' ', $class ) . "'";
		            $data_field = "data-field='{$column_key}' ";

	                $html .=  "<td {$class} $data_field>{$column_data}</td>";
	            }

	            $html .= '</tr>';

	            // Details Block
	            //
	            $column_count = count( $this->columns );

	            $sqlParams = array( ++$offset );
	        }

	        $tableHeaderString = $this->createstring_TableHeader() ;

	        $html =
	            "<table id='manage_locations_table' class='slplus wp-list-table widefat posts' cellspacing=0>" .
	            $tableHeaderString  .
	            $html               .
                $tableHeaderString  .
	            '</table>'          .
	            $this->create_string_hidden_fields()
	            ;

	        return $html;
	    }

	    /**
	     * Create the pagination string for the manage locations table.
	     *
	     * @return string
	     */
	    private function createstring_PanelManageTablePagination() {
	        if ($this->total_locations_shown >0) {
	            return $this->createstring_PaginationBlock(
	                $this->total_locations_shown,
		            $this->slplus->options_nojs['admin_locations_per_page'],
	                $this->start
	                );
	        } else {
	            return '';
	        }
	    }

	    /**
	     * Build the HTML for the top-of-table navigation interface.
	     *
	     * @return string
	     */
	    private function createstring_PanelManageTableTopActions() {
	        $HTML =
	            $this->createstring_BulkActionsBlock()           .
	            $this->createstring_FiltersBlock()               .
	            $this->createstring_DisplayBlock()               .
	            $this->createstring_SearchBlock()                .
	            $this->createstring_DeleteColumnsBlock()         .
	            $this->createstring_PanelManageTablePagination() ;

		    /**
		     * Filter to add stuff to the manage locations action bar.
		     *
		     * @filter  slp_manage_locations_actionbar_ui
		     *
		     * @params  string  HTML
		     */
		    return
	            '<div class="tablenav top">'                            .
	            apply_filters( 'slp_manage_locations_actionbar_ui', $HTML ) .
	            '</div>'                                                ;
	    }

		/**
		 * Create the delete columns droppable in the manage locations header.
		 */
		private function createstring_DeleteColumnsBlock() {
			$hider_text = __( 'Drag colum here to hide it.' , 'store-locator-le' );
			return
				'<div class="alignleft actions">' .
					"<span id='column_hider' class='dashicons dashicons-editor-contract' alt='{$hider_text}' title='{$hider_text}'></span>" .
		        '</div>'
				;
		}

	    /**
	     * Create the display drop down for the top-of-table navigation.
	     *
	     */
	    private function createstring_SearchBlock() {
	        $currentSearch = ((isset($_REQUEST['searchfor'])&&!empty($_REQUEST['searchfor']))?$_REQUEST['searchfor']:'');

			if ( ! empty( $currentSearch ) ) {
			  $currentSearch =
				   htmlentities(
					  stripslashes_deep( $currentSearch ),
						ENT_QUOTES
				   );
			}

			return
	            '<div class="alignleft actions">'                                                                               .
	                "<input id='searchfor' value='{$currentSearch}' type='text' name='searchfor' "                              .
	                    ' onkeypress=\'if (event.keyCode == 13) { event.preventDefault();AdminUI.doAction("search",""); } \' '              .
	                    ' />'                                                                                                   .
	                "<input id='doaction_search' class='button action' type='submit' "                                          .
	                    "value='".__('Search','store-locator-le')."' "                                                                .
	                    'onClick="AdminUI.doAction(\'search\',\'\');" '                                             .
	                    ' />'                                                                                                   .
	            '</div>'
	            ;
	    }

	    /**
	     * Create the manage locations table header string.
	     *
	     * @return string the HTML string
	     */
	    function createstring_TableHeader() {
	        return
	            '<thead>'                                                                                   .
	                '<tr >'                                                                                 .
	                    "<th id='top_of_checkbox_column'>"                                                  .
	                        '<input type="checkbox"  '                                                      .
	                            'onclick="'                                                                 .
	                                "jQuery('.slp_checkbox').prop('checked',jQuery(this).prop('checked'));" .
	                                '" '                                                                    .
	                            '>'                                                                         .
	                    '</th>' .
	                    $this->create_string_column_headers() .
	                '</tr>' .
	                '</thead>'
	                ;
	    }

		/**
		 * Create the column headers string.
		 *
		 * @param boolean $with_id Show ID on column header. (default: true)
		 *
		 * @return string
		 */
		function create_string_column_headers( $with_id = true ) {
			list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();
			$html = '';

			foreach ( $columns as $column_key => $column_display_name ) {
				$class = array( 'manage-column', "column-$column_key" );

				if ( in_array( $column_key, $hidden ) ) {
					$class[] = 'hidden';
				}

				if ( ! $this->script_data[ 'all_displayed' ]  && ( $this->db_orderbyfield === $column_display_name ) ) {
					$class[] = 'sorted ';
					$class[] = $this->sort_order;
				}

				// Sortable Header If Partial Location List
				//
				if ( ! $this->script_data[ 'all_displayed' ] ) {
					$newDir = ( $this->sort_order === 'asc' ) ? 'desc' : 'asc';
					$cell_content =
						"<a href='{$this->cleanURL}&orderBy=$column_key&sortorder=$newDir' alt='{$column_display_name}' title='{$column_display_name}'>" .
						"<span>$column_display_name</span>".
						"<span class='sorting-indicator'></span>" .
						"</a>";

					// All locations shown, use JavaScript UI manager DataTables.
					//
				} else {
					$cell_content = "<a href='#'>$column_display_name</a>";
				}

				$tag        = ( 'cb' === $column_key ) ? 'td' : 'th';
				$scope      = ( 'th' === $tag ) ? 'scope="col"' : '';
				$class      = "class='" . join( ' ', $class ) . "'";
				$data_field = "data-field='{$column_key}' ";

				$html .= "<{$tag} {$scope} {$class} {$data_field}>{$cell_content}</{$tag}>";
			}

			return $html;
		}

		/**
		 * Show hidden fields list.
		 *
		 * @return string
		 */
		private function create_string_hidden_fields() {
			list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();
			if ( ! empty( $hidden ) ) {
				$html = __('Hidden Fields: ', 'store-locator-le' );
				foreach ( $columns as $column_key => $column_display_name ) {
					if ( ! in_array( $column_key, $hidden ) ) { continue; }
					$restore_message = __('Click to restore %s.','store-locator-le');
					$restore_message = sprintf( $restore_message , $column_display_name);
					$html .= sprintf( '<a href="#" alt="%s" title="%s"><span class="unhider" id="%s" data-field="%s">%s</span></a>' ,
						$restore_message, $restore_message, $column_key, $column_key, $column_display_name
						);
				}
				$html = "<div id='location_unhider'>{$html}</div>";
			} else {
				$html ='';
			}
			return  $html;
		}

	    /**
	     * Process any incoming actions.
	     */
	    function process_Actions() {
	        if ( $this->current_action === '' ) { return; }

	        switch ($this->current_action) {

	            // ADD
	            //
	            case 'add' :
	                $this->location_Add();
	                $this->slplus->notifications->display();
	                break;

	            // SAVE
	            //
	            case 'edit':
	                if ( $this->slplus->currentLocation->isvalid_ID( null, 'id' ) ) {
	                    $this->location_save();
	                }
	               $_REQUEST['selected_nav_element'] = '#wpcsl-option-edit_location';
	                break;

	            case 'save':
	               $this->location_save();
	               $_REQUEST['selected_nav_element'] = '#wpcsl-option-current_locations';
	                break;

	            // DELETE
	            //
	            case 'delete':
	                if ( ! isset($_REQUEST['sl_id'] ) && isset( $_REQUEST['id'] ) ) {
	                    $locationList = (array) $_REQUEST['id'];
	                } else {
	                    $locationList = is_array($_REQUEST['sl_id'])?$_REQUEST['sl_id']:array($_REQUEST['sl_id']);
	                }
	                foreach ($locationList as $locationID) {
	                    $this->slplus->currentLocation->set_PropertiesViaDB($locationID);
	                    $this->slplus->currentLocation->DeletePermanently();
	                }
	                break;

	            // Locations Per Page Action
	            //   - update the option first,
	            //   - then reload the
	            case 'locationsperpage':
	                $newLimit = preg_replace('/\D/','',$_REQUEST['admin_locations_per_page']);
	                if (ctype_digit($newLimit) && (int)$newLimit > 9) {
		                $this->slplus->options_nojs['admin_locations_per_page'] = $newLimit;
		                update_option(SLPLUS_PREFIX . '-options_nojs', $this->slplus->options_nojs);
	                }

		            break;

	            // All other things do nothing here...
	            // But they may be handled by an add-on pack via the
	            // FILTER: slp_manage_locations_action
	            //
	            default:
	                break;
	        }

		    /**
		     * Hook executes when processing a manage locations action.
		     *
		     * @action  slp_manage_locations_action
		     */
	        do_action('slp_manage_locations_action');
	    }

	    /**
	     * Render the manage locations admin page.
	     *
	     */
	    function render_adminpage() {
	        $this->process_Actions();

	        //------------------------------------------------------------------------
	        // CHANGE UPDATER
	        // Changing Updater
	        //------------------------------------------------------------------------
	        if (isset($_GET['changeUpdater']) && ($_GET['changeUpdater']==1)) {
	            if (get_option('sl_location_updater_type')=="Tagging") {
	                update_option('sl_location_updater_type', 'Multiple Fields');
	                $updaterTypeText="Multiple Fields";
	            } else {
	                update_option('sl_location_updater_type', 'Tagging');
	                $updaterTypeText="Tagging";
	            }
	            $_SERVER['REQUEST_URI']=preg_replace('/&changeUpdater=1/', '', $_SERVER['REQUEST_URI']);
	            print "<script>location.replace('".$_SERVER['REQUEST_URI']."');</script>";
	        }

	        //------------------------------------
	        // Create Location Panels
	        //
	        add_action('slp_build_locations_panels',array($this,'create_settings_section_Manage'  ),10);
	        add_action('slp_build_locations_panels',array($this,'create_settings_section_Add'     ),20);

	        //-------------------------
	        // Setup Navigation Bar
	        //
	        $this->settings->add_section(
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

		    /**
		     * Hook executes when building the locations panels, before rendering.
		     *
		     * @action slp_build_locations_panels
		     */
	        do_action('slp_build_locations_panels');

		    $this->enqueue_scripts();

	        $this->settings->render_settings_page();
	    }


	    /**
	     * Set filters for manage location formatting.
	     */
	    function set_manage_locations_formatting() {

	        // Highlight invalid locations
	        //
	        add_filter('slp_locations_manage_cssclass',array($this,'filter_InvalidHighlight'  ) , 10 );
	        add_filter('slp_locations_manage_cssclass',array($this,'add_private_location_css' ) , 15 );

	        // Add lat/long to the name field
	        //
	        add_filter( 'slp_column_data' , array($this, 'filter_AddLatLongUnderName'           ) , 10, 3 );
	        add_filter( 'slp_column_data' , array($this, 'add_private_text_under_name'          ) , 11, 3 );

	        // Add Image to the output columns
	        //
	        add_filter( 'slp_column_data' , array($this, 'filter_AddImageToManageLocations'     ), 90 ,  3  );


	    }
	}
}
// Dad. Explorer. Rum Lover. Code Geek. Not necessarily in that order.
