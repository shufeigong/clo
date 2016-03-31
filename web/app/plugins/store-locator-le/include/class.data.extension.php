<?php

if ( ! class_exists('SLPlus_Data_Extension') ) {

	/**
	 * The extended data interface helper.  Managed the extended data columns when needed.
	 *
	 * @package StoreLocatorPlus\Data\Extension
	 * @author Lance Cleveland <lance@charlestonsw.com>
	 * @copyright 2014 - 2016 Charleston Software Associates, LLC
	 *
	 * @property        SLPlus_Data             $database
	 * @property        metatable['records'][]  $active_columns     The active extended data columns (does not include inactive addon packs)
	 *
	 */
	class SLPlus_Data_Extension extends SLPlus_BaseClass_Object  {
		private $active_columns;
		public $database;

	    /**
	     * The properties of the meta table.
	     *
	     * metatable['name']
	     *
	     * metatable['records'][<slug>][id|field_id|label|slug|type|options]
	     *
	     * - name = the name of the meta table.
	     * - records = a named array, keys are field slugs => values are named arrays of the properties
	     *
	     *   - <slug> the field slug is the key
	     *
	     *       - id = the unique id for this field
	     *       - field_id = the unique id as a string field_###
	     *       - label = the proper case label
	     *       - slug = the "slugified" version of the label
	     *       - type = the field type varchar(default)/text/int/boolean
	     *       - options (serialized)     *
	     *
	     * @var string[] $metatable
	     */
	    var $metatable;

	    /**
	     * Properties of the plugin data table.
	     *
	     * 'name'   = table name
	     * 'fields' = key/value pair key = field name, value = field format
	     *
	     * @var string[] $plugintable
	     */
	    public $plugintable;

		/**
		 * Things we do when be build this.
		 */
	    function initialize( ) {
	        $this->metatable['name'] = $this->slplus->db->prefix . 'slp_extendo_meta';
	        $this->metatable['records'] = array();
	        $this->plugintable['name'] = $this->slplus->db->prefix . 'slp_extendo';
	        $this->plugintable['fields'] = array(
	            'id' => '%u',
	            'sl_id' => '%u',
	            'value' => '%s'
	        );

		    add_filter('slp_extend_get_SQL'             , array($this, 'filter_ExtendedDataQueries'));
	    }

	    /**
	     * Adds a field to the data table, if already exits then update it.
	     *
	     * mode parameter
	     * - 'immediate' = default, run create table command when adding the field
	     * - 'wait' = do not run the create table command when adding this field
	     *
	     * @param string  	$label    	Plain text field label for this field.
	     * @param string  	$type     	The data type for this field.
	     * @param array 	$options 	Options field contains serialized data for this field.
		 * 						@key	string	'slug'				Unique field slug
		 * 						@key	string	'addon'				Slug for the add-on object (short slug i.e. 'slp-experience')
		 * 						@key	string	'display_type' 		Display helper used to help with admin and UI rendering.
		 *
	     * @param string  	$mode     'wait' or 'immediate' determines if we call the update table structure as soon as this is called.
	     *
	     * @return string the slug of the field that was added.
	     */
	    function add_field($label, $type = 'text', $options = array(), $mode = 'wait') {
	        // Check whether slug is provided in $options
	        //
	        if ( isset( $options['slug'] ) && !empty( $options['slug'] ) ) {
		        $slug = $options['slug'];

	        } else {
		        add_filter('sanitize_title', array($this, 'filter_SanitizeTitleForMySQLField'), 10, 3);
	            $slug = sanitize_title($label, '', 'save');
				$options['slug'] = $slug;
		        remove_filter('sanitize_title', array($this, 'filter_SanitizeTitleForMySQLField'));
	        }

	        // Check if slug already exists before adding it.
	        //
	        if ( ! $this->has_field( $slug ) ) {
		        $nextval = $this->slplus->options_nojs['next_field_id'] ++;
		        $nextval = str_pad( $nextval, 3, "0", STR_PAD_LEFT );
		        update_option( SLPLUS_PREFIX . '-options_nojs', $this->slplus->options_nojs );

		        $this->slplus->db->insert(
			        $this->metatable['name'],
			        array(
				        'field_id' => 'field_' . $nextval,
				        'label'    => $label,
				        'slug'     => $slug,
				        'options'  => maybe_serialize( $options ),
				        'type'     => $type
			        )
		        );

		    // If exists, update it.
		    //
            } else {
		        $this->update_field( $label, $type , $options);
	        }
		    
		    if ($mode === 'immediate') {
			    $this->update_data_table(array('mode' => 'force'));
		    }
	        return $slug;
	    }

	    /**
	     * Removes a field from the data table
	     *
	     * mode parameter
	     * - 'immediate' = default, run update table command when removing the field
	     * - 'wait' = do not run the update table command when removing this field
	     *
	     * @param $label string The label to remove
	     * @param $options mixed[] wpdb options
	     * @param $mode string operating mode
	     *
	     * @return string slug of the removed field.
	     */
	    function remove_field($label, $options = array(), $mode = 'immediate') {

	        // Check whether a slug is provided in $options
	        add_filter('sanitize_title', array($this, 'filter_SanitizeTitleForMySQLField'), 10, 3);
	        if (isset($options['slug']) && (trim($options['slug']) !== '')) {
	            $slug = $options['slug'];
	        } else {
	            $slug = sanitize_title($label, '', 'save');
	        }
	        remove_filter('sanitize_title', array($this, 'filter_SanitizeTitleForMySQLField'));

	        // Check if slug exists before removing it.
	        //
	            if ($this->has_field($slug)) {
	            $this->slplus->db->delete($this->metatable['name'], array('slug' => $slug));
	            if ($mode === 'immediate') {
	                $this->update_data_table(array('mode' => 'force'));
	            }
	        }

	        return $slug;
	    }

	    /**
	     * Extend the SQL query set for extended data queries.
	     *
	     * @param string $command
	     * @return string
	     */
	    function filter_ExtendedDataQueries($command) {
	        switch ($command) {
	            // SELECT
	            //
	            case 'select_all_from_extendo':
	                return "SELECT * FROM {$this->metatable['name']}";

                case 'select_slid_from_extended_data':
                    return "SELECT sl_id FROM {$this->plugintable['name']}";

	            // JOIN
	            //
	            case 'join_extendo':
	                return ' LEFT JOIN ' . $this->plugintable['name'] . ' USING(sl_id) ';

	            // WHERE
	            //
	            case 'where_slugis':
	                return ' WHERE slug = %s ';

	            // DEFAULT
	            //
	            default:
	                return $command;
	        }
	    }

	    /**
	     * Add the join clause to the base plugin select all clause.
	     *
	     * @param string $sqlStatement the existing SQL command for Select All
	     * @return string
	     */
	    function filter_ExtendSelectAll($sqlStatement) {
	        if ( strpos($sqlStatement , ' LEFT JOIN ' . $this->plugintable['name'] . ' USING(sl_id) ' ) !== false ) {
	            return $sqlStatement;
	        }
	        return $sqlStatement . $this->filter_ExtendedDataQueries('join_extendo');
	    }

	    /**
	     * Replace hyphens with underscore to make "titles" MySQL field name appropriate.
	     *
	     * @param string $title party cleaned up title
	     * @param string $raw_title original title
	     * @param string $context mode that sanitize_title was called with such as 'query' or 'save'
	     * @return string sanitized title string with no hyphens in it
	     */
	    function filter_SanitizeTitleForMySQLField($title, $raw_title, $context) {
	        return str_replace('-', '_', $title);
	    }

	    /**
	     * Reads the metadata from the slp_extendo_meta table as OBJECTS and stores it in metatable['records'][<slug>]
	     *
	     * @param boolean $force = set true to force reloading of data.
	     */
	    function set_cols($force = false) {
	        if (( count($this->metatable['records']) === 0 ) || $force) {
	            $meta_data = $this->slplus->db->get_results("SELECT * FROM {$this->metatable['name']}", OBJECT);
		        $this->metatable['records'] = array();
	            foreach ($meta_data as $field) {
	                $this->metatable['records'][$field->slug] = $field;
	            }
	        }
	    }

		/**
		 * Get the active columns (those for active add-on packs.
		 *
		 * @return stdClass[] columns for active plugins.
		 */
		public function get_active_cols() {
			if ( !isset ( $this->active_columns ) ) {
				$this->set_cols();
				$active_cols = array();

				foreach ( $this->metatable['records'] as $slug => $field_meta ) {
					$field_options = maybe_unserialize( $field_meta->options );

					// Addon property set, but is not in active instances list
					// skip it.
					if (   isset( $field_options['addon'] ) &&
						 ! isset( $this->slplus->add_ons->instances[ $field_options['addon'] ] )
						) {
						continue;
					}
					$active_cols[$slug] = $field_meta;
					$active_cols[$slug]->option_values = $field_options;
				}
				$this->active_columns = $active_cols;
			}
			return $this->active_columns;
		}

	    /**
	     * Return an array of the meta data field properties.
	     *
	     * @param boolean $force force a re-read of the meta data from disk.
	     * @return array an array of arrays containing the meta data field values.
	     */
	    function get_cols($force = false) {
	        $this->set_cols($force);
	        return array_values($this->metatable['records']);
	    }

	    /**
	     * Gets data for a store id, useful in cases when a join isn't required
	     * @param $sl_id int The id to lookup
	     * @param $field_id string (optional) The field id to return when only one field is needed
	     * @return mixed The column (string) or an array of all the columns
	     */
	    function get_data($sl_id, $field_id = null) {
	        global $wpdb;
	        $query = $wpdb->prepare("select * from {$this->plugintable['name']} where sl_id = %s", $sl_id);
	        $cols = $wpdb->get_results($query, ARRAY_A);
	        if ($cols === null) {
	            return array();
	        }
	        if (count($cols) < 1) {
	            return array();
	        }

	        if (isset($field_id)) {
	            return $cols[0][$field_id];
	        }

	        return $cols[0];
	    }

		/**
		 * Get the options set for an extended data field.
		 *
		 * @param 	string 	$slug			the field slug
		 * @param 	string 	$option_name	the option name
		 *
		 * @return	mixed					the value of that option
		 */
		function get_option( $slug , $option_name ) {
			$this->get_active_cols();
			if ( ! isset( $this->active_columns[$slug] ) ) { return null; }

			$this->set_options( $slug );
			if ( ! isset( $this->active_columns[$slug]->option_values[$option_name] ) ) { return null; }

			return $this->active_columns[$slug]->option_values[$option_name];
		}

		/**
		 * Set the option-values for a field by breaking apart the serialized options part of the meta.
		 *
		 * @param $slug
		 */
		public function set_options( $slug ) {
			if ( ! isset( $this->active_columns[$slug] 				) ) { return; }
			if ( isset( $this->active_columns[$slug]->option_values ) ) { return; }
			if ( ! isset( $this->active_columns[$slug]->options 	) ) { return; }
			$this->active_columns[$slug]->option_values = maybe_unserialize( $this->active_columns[$slug]->options );
		}

	    /**
	     * Tell people if the extended data contains a field identified by slug.
	     *
	     * @param string $slug the field slug
	     * @return boolean true if the field exists, false if not.
	     */
	    function has_field($slug) {
	        if ( ! isset( $this->metatable['records'][$slug] ) ) {
		        $sql = $this->database->get_SQL(array('select_all_from_extendo', 'where_slugis')  );
	            $slug_data = $this->database->get_Record(array('select_all_from_extendo', 'where_slugis'), $slug, 0 , OBJECT);
	            if ( is_object( $slug_data ) && ( $slug_data->slug == $slug ) ) {
	                $this->metatable['records'][$slug] = $slug_data;
	            }
	        }
	        return ( isset($this->metatable['records'][$slug]) );
	    }

	    /**
	     * Update an sl_id's data
		 *
	     * @param $sl_id int The id of the location
	     * @param $data mixed The col => value pairs to update
		 *
		 * @return false|int	false if update/insert failed or number of records inserted/updated if OK.
	     */
	    function update_data($sl_id, $data) {
	        global $wpdb;

	        $currentData = $this->get_data($sl_id);

	        // No Current Data?  Insert
	        //
	        if ( ($currentData === null) || ( count($currentData) <= 0 ) ) {
	            $data['sl_id'] = $sl_id;
	            $changed_record_count = $wpdb->insert($this->plugintable['name'], $data);
	        } else {
	            $data = array_merge($currentData, $data);
	            $changed_record_count = $wpdb->update($this->plugintable['name'], $data, array('sl_id' => $sl_id));
	        }

			return $changed_record_count;
	    }

	    /**
	     * Updates the meta data table used to control the field info in the extension data table.
	     *
	     * Table is created or modified whenever a new data field is added.
	     *
	     * Accepted $params values
	     *
	     * - 'mode' determines which mode to operate in:
	     *
	     *  - 'force' = force re-read of metadata
	     *
	     *  - null    = default, use in-memory cache of metadata to build create SQL string
	     *
	     * @global array $EZSQL_ERROR
	     * @param array $params
	     *
	     */
	    function update_data_table($params = array()) {
	        $extended_fields = $this->get_cols(isset($params['mode']) && ( $params['mode'] == 'force'));

	        // If we have some extended data fields...
	        //
	        if ( is_array( $extended_fields) && ( count( $extended_fields ) > 0 )) {
	            $create = "CREATE TABLE {$this->plugintable['name']} (
	            id mediumint(8) NOT NULL AUTO_INCREMENT,
	            sl_id mediumint(8) UNSIGNED NOT NULL,
	            ";
	            foreach ($extended_fields as $field) {
	                if ( is_object( $field ) ) {
	                    switch ($field->type) {
	                        case 'text':
	                            $type = 'longtext';
	                            break;
	                        case 'varchar':
	                            $type = 'varchar(250)';
	                            break;
	                        default:
	                            $type = $field->type;
	                            break;
	                    }

	                    $create .= $field->slug . " $type" . ",\n";
	                }
	            }

	            $create .=
		            "KEY sl_id (sl_id),
	                KEY id (id),
	                KEY slid_id (sl_id,id)
	                ) {$this->database->collate}";

	            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	            dbDelta($create);
	            global $EZSQL_ERROR;
	            $EZSQL_ERROR = array();

	            // Set the plugin "has extended data" property.
	            //
	            $this->slplus->options_nojs['has_extended_data'] = '1';
	            update_option(SLPLUS_PREFIX . '-options_nojs', $this->slplus->options_nojs);

	            // No extended data fields
	        //
	        } else {
	            $this->slplus->options_nojs['has_extended_data'] = '0';
	            update_option(SLPLUS_PREFIX . '-options_nojs', $this->slplus->options_nojs);
	        }
	    }

		/**
		 * Update a data field, changing the metadata.  Provide the slug and the options.
		 *
		 * @param boolean|string    $label            The new value for the label, set to false to skip.
		 * @param boolean|string    $type             The new value for the type, set to false to skip
		 * @param array             $options_array    The options array stored in the metadata options field.
		 *
		 * @return boolean|int|string  returns false if failed, 1 if update made, slug if field added.
		 */
		function update_field( $label , $type , $options_array ) {
			if ( !isset( $options_array['slug'] ) || empty( $options_array['slug'] ) ) { return; }
			$slug = $options_array['slug'];

			// Prepare new data array
			$data_array = array();
			if ( $label !== false ) { $data_array['label'] = $label; }
			if ( $type  !== false ) { $data_array['type']  = $type;  }

			// From 4.3 support rename of old_slug to slug
			//
			$old_slug = '';
			if ( isset( $options_array['old_slug'] ) ) {
				$old_slug = $options_array['old_slug'];
				if ( ! empty($old_slug) ) {
					$new_slug = $this->update_field_slug( $slug , $old_slug , $type );
					$data_array['slug']  = $slug;
				}
				unset($options_array['old_slug']);
			} else {

				// Pre 4.1.02 - the slug was the label, sanitized.  Change it to a proper slug.
				//
				if ( ! $this->has_field( $slug ) && ( $label !== false ) && ( $type !== false ) ) {
					$old_slug = $this->update_field_slug( $slug , $label , $type );
					$data_array['slug']  = $slug;
				}
			}

			// All Versions
			//
			if ( $this->has_field( $slug ) ) {
				$existing_options = maybe_unserialize($this->metatable['records'][$slug]->options);
				$field_id = $this->metatable['records'][$slug]->field_id;
			} elseif ( $this->has_field( $old_slug ) ) {
				$existing_options = maybe_unserialize($this->metatable['records'][$old_slug]->options);
				$field_id = $this->metatable['records'][$old_slug]->field_id;
			} else {
				return false;
			}
			$options_array = array_merge( $options_array , array( 'slug' => $slug ));

			// Mix existing_options with new ones in options_array
			$data_array['options'] = maybe_serialize( array_merge( $existing_options, $options_array) );

			// Update the metadata
			//
			return
				$this->slplus->db->update(
					$this->metatable['name'] ,          // table
					$data_array              ,          // data
					array( 'field_id' => $field_id )    // where
				);
		}

		/**'
		 * Update the pre 4.1.02 field slugs.
		 *
		 * @param string $slug
		 * @param string $label
		 * @param string $type
         * @return string
		 */
		function update_field_slug( $slug , $label , $type ) {
			$old_slug = $slug;
			add_filter('sanitize_title', array($this, 'filter_SanitizeTitleForMySQLField'), 10, 3);
			if ( $this->has_field( sanitize_title( $label ) ) ) {
				$old_slug = sanitize_title( $label, '', 'save' );
				if ( $type === 'varchar' ) { $type = 'varchar(250)'; }
				$sql_command =
					sprintf(
						'ALTER TABLE %s change %s %s %s',
						$this->plugintable['name'],
						$old_slug,
						$slug,
						$type
					);
				$this->slplus->db->query( $sql_command );
			}
			remove_filter( 'sanitize_title', array( $this, 'filter_SanitizeTitleForMySQLField' ) );
			return $old_slug;
		}

		//----------------------------------------------------
		// DEPRECATED
		//----------------------------------------------------

		/**
		 * Return true if the database is extended and has an extended data table with records in it.
         *
         * TODO: remove from GFI
		 *
		 * @deprecated  Use ->slplus->database->has_extended_data()
		 *
		 * @return boolean
		 */
		public function has_ExtendedData() {
			return $this->database->has_extended_data();
		}
	}

}