<?php
if (! class_exists('Tagalong_Activation')) {
	require_once( SLPLUS_PLUGINDIR . '/include/base_class.activation.php' );

	/**
	 * Manage plugin activation.
	 *
	 * @package StoreLocatorPlus\Tagalong\Activation
	 * @author Lance Cleveland <lance@charlestonsw.com>
	 * @copyright 2013 - 2014 Charleston Software Associates, LLC
	 *
	 */
	class Tagalong_Activation extends SLP_BaseClass_Activation {

		//----------------------------------
		// Properties
		//----------------------------------

		/**
		 * The plugin object
		 *
		 * @var \SLPTagalong $addon
		 */
		var $addon;

		//----------------------------------
		// Methods
		//----------------------------------

		/**
		 * Update the data structures on new db versions.
		 *
		 * @global object $wpdb
		 *
		 * @param type $sql
		 * @param type $table_name
		 *
		 * @return string
		 */
		function dbupdater( $sql, $table_name ) {
			$this->addon->data->set_DB_charset();
			$retval = ( $this->slplus->db->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) ? 'new' : 'updated';
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
			global $EZSQL_ERROR;
			$EZSQL_ERROR = array();
			return $retval;
		}

		/**
		 * Install or update the main table
		 * @global object $wpdb
		 */
		function create_MoreInfoTable() {
			$this->dbupdater(
				$this->addon->data->get_SQL( 'create_tagalong_helper' ),
				$this->addon->data->plugintable['name']
			);
		}

		/**
		 * Update or create the data tables.
		 *
		 * This can be run as a static function or as a class method.
		 */
		function update() {
			$this->create_MoreInfoTable();

			// If the installed version is < 1.0, load up the new category table
			//
			if ( version_compare( $this->addon->options['installed_version'], '1.1', '<' ) ) {
				$theSQL = $this->addon->data->get_SQL( 'select_where_optionvalue_has_cats' );
				$offset = 0;
				while ( ( $location = $this->addon->data->db->get_row( $theSQL, ARRAY_A, $offset ++ ) ) != null ) {
					$optionValues = maybe_unserialize( $location['sl_option_value'] );
					foreach ( $optionValues['store_categories']['stores'] as $categoryID ) {
						$this->addon->data->add_RecordIfNeeded( $location['sl_id'], $categoryID );
					}
				}
			}
		}
	}
}