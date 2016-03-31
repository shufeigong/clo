<?php
if (! class_exists('SLP_Admin_Helper')) {

	/**
	 * Helper, non-critical methods to make WordPress plugins easier to manage.
	 *
	 * @package StoreLocatorPlus\Admin\Helper
	 * @author Lance Cleveland <lance@charlestonsw.com>
	 * @copyright 2016 Charleston Software Associates, LLC
	 */
	class SLP_Admin_Helper extends SLPlus_BaseClass_Object {

		/**
		 * Return the icon selector HTML for the icon images in saved markers and default icon directories.
		 *
		 * @param string $field_id
		 * @param string $image_id
		 * @return string
		 */
		function create_string_icon_selector($field_id , $image_id ) {
			if ( is_null($field_id ) || is_null( $image_id ) ) { return ''; }

			$htmlStr = '';
			$files=array();
			$fqURL=array();

			// If we already got a list of icons and URLS, just use those
			//
			if (
				isset($this->slplus->data['iconselector_files']) &&
				isset($this->slplus->data['iconselector_urls'] )
			) {
				$files = $this->slplus->data['iconselector_files'];
				$fqURL = $this->slplus->data['iconselector_urls'];

				// If not, build the icon info but remember it for later
				// this helps cut down looping directory info twice (time consuming)
				// for things like home and end icon processing.
				//
			} else {

				// Load the file list from our directories
				//
				// using the same array for all allows us to collapse files by
				// same name, last directory in is highest precedence.
				$iconAssets = apply_filters('slp_icon_directories',
					array(
						array('dir'=>SLPLUS_UPLOADDIR.'saved-icons/',
							'url'=>SLPLUS_UPLOADURL.'saved-icons/'
						),
						array('dir'=>SLPLUS_ICONDIR,
							'url'=>SLPLUS_ICONURL
						)
					)
				);
				$fqURLIndex = 0;
				foreach ($iconAssets as $icon) {
					if (is_dir($icon['dir'])) {
						if ($iconDir=opendir($icon['dir'])) {
							$fqURL[] = $icon['url'];
							while ($filename = readdir($iconDir)) {
								if (strpos($filename,'.')===0) { continue; }
								$files[$filename] = $fqURLIndex;
							};
							closedir($iconDir);
							$fqURLIndex++;
						} else {
							$this->slplus->notifications->add_notice(
								9,
								sprintf(
									__('Could not read icon directory %s','store-locator-le'),
									$icon['dir']
								)
							);
							$this->slplus->notifications->display();
						}
					}
				}
				ksort($files);
				$this->slplus->data['iconselector_files'] = $files;
				$this->slplus->data['iconselector_urls']  = $fqURL;
			}

			// Build our icon array now that we have a full file list.
			//
			foreach ($files as $filename => $fqURLIndex) {
				if (
					(preg_match('/\.(png|gif|jpg)/i', $filename) > 0) &&
					(preg_match('/shadow\.(png|gif|jpg)/i', $filename) <= 0)
				) {
					$htmlStr .=
						"<div class='slp_icon_selector_box'>".
						"<img
	                         	 data-filename='$filename'
	                        	 class='slp_icon_selector'
	                             src='".$fqURL[$fqURLIndex].$filename."'
	                             onclick='".
						"document.getElementById(\"".$field_id."\").value=this.src;".
						"document.getElementById(\"".$image_id."\").src=this.src;".
						"'>".
						"</div>"
					;
				}
			}

			// Wrap it in a div
			//
			if ($htmlStr != '') {
				$htmlStr = '<div id="'.$field_id.'_icon_row" class="slp_icon_row">'.$htmlStr.'</div>';

			}


			return $htmlStr;
		}
	}
}