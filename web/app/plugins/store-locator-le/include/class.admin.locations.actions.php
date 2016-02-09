<?php
if ( ! class_exists('SLPlus_Admin_Locations_Actions') ) {

	/**
	 * Admin Locations Tab Actions Processing
	 *
	 * @package   StoreLocatorPlus\Admin\Locations\Actions
	 * @author    Lance Cleveland <lance@charlestonsw.com>
	 * @copyright 2016 Charleston Software Associates, LLC
	 *
	 * @property		SLPlus_AdminUI_Locations	$screen		The screen we are processing.
	 */
	class SLPlus_Admin_Locations_Actions extends SLPlus_BaseClass_Object {
		public		$screen;

		// Add a locations
		//
		private function add_location() {

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
					( $this->screen->current_action === 'add'     ) &&
					( isset($locationData['sl_latitude'  ]) && is_numeric($locationData['sl_latitude'  ])    ) &&
					( isset($locationData['sl_longitude' ]) && is_numeric($locationData['sl_longitude' ])    )
				;
				$response_code = $this->slplus->currentLocation->add_to_database( $locationData , 'none' , $skipGeocode );

				$this->slplus->notifications->add_notice( 'info' ,
					stripslashes_deep($_POST['store-']) . ' ' .
					$this->slplus->text_manager->get_variable_admin_text( 'successfully_completed' , $response_code )
				);

			} else {
				$this->slplus->notifications->add_notice( 'info' ,
					$this->slplus->text_manager->get_admin_text( 'location_not_added' ) .
					$this->slplus->text_manager->get_admin_text( 'location_form_incorrect' )

				);

			}
		}

		/**
		 * Delete extended data.
		 *
		 * Hooks onto slp_deletelocation_starting
		 *
		 * TODO: check this, it should probably be run ALWAYS from where that hook is called.
		 */
		public function delete_location_extended_data() {
			$this->slplus->db->delete(
				$this->slplus->database->extension->plugintable['name'],
				array( 'sl_id' => $this->slplus->currentLocation->id )
			);
		}

		/**
		 * Process any incoming actions.
		 */
		function process_actions() {
			if ( empty( $this->screen->current_action ) ) { return; }

			switch ($this->screen->current_action) {

				// ADD
				//
				case 'add' :
					$this->add_location();
					$this->slplus->notifications->display();
					break;

				// SAVE
				//
				case 'edit':
					if ( $this->slplus->currentLocation->isvalid_ID( null, 'id' ) ) {
						$this->save_location(); // TODO: Why is the location being saved on EDIT action when it is starting?
					}
					$_REQUEST['selected_nav_element'] = '#wpcsl-option-edit_location';
					break;

				case 'save':
					$this->save_location();
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
				//
				// TODO: Move admin_locations_per_page to WP user meta
				//
				case 'locationsperpage':
					$newLimit = preg_replace('/\D/','',$_REQUEST['admin_locations_per_page']);
					if (ctype_digit($newLimit) && (int)$newLimit > 9) {
						$this->slplus->options_nojs['admin_locations_per_page'] = $newLimit;
						update_option(SLPLUS_PREFIX . '-options_nojs', $this->slplus->options_nojs);
					}

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
		 * Save a location.
		 */
		function save_location() {
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
			 * HOOK: slp_location_save
			 *
			 * Executes when a location save action is called from manage locations.
			 *
			 * @action slp_location_save
			 */
			do_action('slp_location_save');
			if ($this->slplus->currentLocation->dataChanged) {
				$this->slplus->currentLocation->MakePersistent();
			}

			/**
			 * HOOK: slp_location_saved
			 *
			 * Executes after a location has been saved from the manage locations interface.
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
		 * Save the extended data.
		 */
		function save_location_extended_data() {
			$action = isset( $_REQUEST['act'] ) ? $_REQUEST['act'] : '';

			// Check our extended column info and see if there is a matching property in exdata in currentLocation
			//
			$newValues = array();
			$this->screen->set_active_columns();
			foreach ( $this->screen->active_columns as $extraColumn ) {
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
	}
}