<?php
/**
 * This takes care of all the options for Auto Hide Admin Bar
 *
 * @author Marcel Bootsman
 * @link   http://www.nostromo.nl/wordpress-plugins/auto-hide-admin-bar/
 */

add_action( 'admin_menu', 'ahab_plugin_add_options_page' );
/**
 * Add an option page as subpage to Plugins.
 *
 * @author Marcel Bootsman
 * @link   http://www.nostromo.nl/wordpress-plugins/auto-hide-admin-bar/
 *
 * @param none
 *
 * @return none
 */
function ahab_plugin_add_options_page() {
	add_options_page( 'Auto Hide Admin Bar Options', 'Auto Hide Admin Bar', 'manage_options', 'auto-hide-admin-bar', 'ahab_options_page' );
}

/**
 * Display the plugin options page
 *
 * @author Marcel Bootsman
 * @link   http://www.nostromo.nl/wordpress-plugins/auto-hide-admin-bar/
 *
 * @param none
 *
 * @return none
 */
function ahab_options_page() {
	?>
	<div class="wrap">
		<h2>Auto Hide Admin Bar Options</h2>

		<p>Version: <?php echo plugin_get_version(); ?></p>
		<?php _e( 'Options for Auto Hide Admin Bar.', 'auto-hide-admin-bar' ); ?>
		<form action="options.php" method="post">
			<?php settings_fields( 'ahab_plugin_options' ); ?>
			<?php do_settings_sections( 'ahab_plugin' ); ?>
			<input name="Submit" type="submit" class="button button-primary save"
			       value="<?php _e( 'Save Changes', 'auto-hide-admin-bar' ); ?>" />
		</form>
	</div>
<?php
}

add_action( 'admin_init', 'ahab_plugin_options_init' );
/**
 * Add the plugin options, sections and fields
 *
 * @author Marcel Bootsman
 * @link   http://www.nostromo.nl/wordpress-plugins/auto-hide-admin-bar/
 *
 * @param none
 *
 * @return none
 */
function ahab_plugin_options_init() {
	register_setting( 'ahab_plugin_options', 'ahab_plugin_options', 'ahab_validate_input' );

	add_settings_section( 'ahab_plugin_section_speed', __( 'Set speed', 'auto-hide-admin-bar' ),
		'ahab_plugin_section_speed_text', 'ahab_plugin' );
	add_settings_field( 'ahab_plugin_option_speed', __( 'Animation speed:', 'auto-hide-admin-bar' ),
		'ahab_plugin_setting_speed', 'ahab_plugin', 'ahab_plugin_section_speed' );

	add_settings_section( 'ahab_plugin_section_delay', __( 'Set amount of delay for hiding the Toolbar',
		'auto-hide-admin-bar' ), 'ahab_plugin_section_delay_text', 'ahab_plugin' );
	add_settings_field( 'ahab_plugin_option_delay', __( 'Delay:', 'auto-hide-admin-bar' ), 'ahab_plugin_setting_delay',
		'ahab_plugin', 'ahab_plugin_section_delay' );

	add_settings_section( 'ahab_plugin_section_interval', __( 'Set the interval for mouse polling',
		'auto-hide-admin-bar' ), 'ahab_plugin_section_interval_text', 'ahab_plugin' );
	add_settings_field( 'ahab_plugin_option_interval', __( 'Interval:', 'auto-hide-admin-bar' ),
		'ahab_plugin_setting_interval', 'ahab_plugin', 'ahab_plugin_section_interval' );

	add_settings_section( 'ahab_plugin_section_mobile', __( 'Always show if on mobile (smaller than 782px)',
		'auto-hide-admin-bar' ), 'ahab_plugin_section_mobile_text', 'ahab_plugin' );
	add_settings_field( 'ahab_plugin_option_mobile', __( 'Show or hide on small screens:', 'auto-hide-admin-bar' ),
		'ahab_plugin_setting_mobile', 'ahab_plugin', 'ahab_plugin_section_mobile' );

	add_settings_section( 'ahab_plugin_section_admin', __( 'Hide on admin screens',
		'auto-hide-admin-bar' ), 'ahab_plugin_section_admin_text', 'ahab_plugin' );
	add_settings_field( 'ahab_plugin_option_admin', __( 'Hide on admin screens:', 'auto-hide-admin-bar' ),
		'ahab_plugin_setting_admin', 'ahab_plugin', 'ahab_plugin_section_admin' );
}

/**
 * Output section text for speed
 *
 * @author Marcel Bootsman
 * @link   http://www.nostromo.nl/wordpress-plugins/auto-hide-admin-bar/
 *
 * @param none
 *
 * @return none
 */
function ahab_plugin_section_speed_text() {
	?>
	<p> <?php _e( 'This option allows you to set the animation speed of the hiding/unhiding process. If a non-number is provided, the default value will be used. Provide a number in milliseconds. Default is: 200', 'auto-hide-admin-bar' ); ?> </p>
<?php
}

/**
 * Output section text for delay
 *
 * @author Marcel Bootsman
 * @link   http://www.nostromo.nl/wordpress-plugins/auto-hide-admin-bar/
 *
 * @param none
 *
 * @return none
 */
function ahab_plugin_section_delay_text() {
	?>
	<p> <?php _e( 'This option allows you to set the delay of the hiding process. This makes sure your Toolbar doesn\'t go haywire when moving quickly in the top of your site. If a non-number is provided, the default value will be used. Provide a number in milliseconds. Default is: 1500', 'auto-hide-admin-bar' ); ?> </p>
<?php
}

/**
 * Output section text for interval
 *
 * @author Marcel Bootsman
 * @link   http://www.nostromo.nl/wordpress-plugins/auto-hide-admin-bar/
 *
 * @param none
 *
 * @return none
 */
function ahab_plugin_section_interval_text() {
	?>
	<p> <?php _e( 'The number of milliseconds Auto Hide Admin Bar waits between reading/comparing mouse coordinates. When the user\'s mouse first enters the element its coordinates are recorded. Setting the polling interval higher will increase the delay before the Toolbar gets hidden. If a non-number is provided, the default value will be used. Provide a number in milliseconds. Default is: 100', 'auto-hide-admin-bar' ); ?> </p>
<?php
}

/**
 * Output section text for mobile
 *
 * @author Marcel Bootsman
 * @link   http://www.nostromo.nl/wordpress-plugins/auto-hide-admin-bar/
 *
 * @param none
 *
 * @return none
 */
function ahab_plugin_section_mobile_text() {
	?>
	<p> <?php _e( 'This option allows you to enable or disable the plugin, when on small screens (< 782px). The
    Default is "Hide the Toolbar". The behaviour of the Toolbar in larger screens will not be affected by this option.', 'auto-hide-admin-bar' ); ?> </p>
<?php
}

/**
 * Output section text for admin screens
 *
 * @author Marcel Bootsman
 * @link   http://www.nostromo.nl/wordpress-plugins/auto-hide-admin-bar/
 *
 * @param none
 *
 * @return none
 */
function ahab_plugin_section_admin_text() {
	?>
	<p> <?php _e( 'This option allows you to hide or always show the Toolbar in admin screens. Default is "Always show the Toolbar"', 'auto-hide-admin-bar' ); ?> </p>
<?php
}

/**
 * Output input field for speed
 *
 * @author Marcel Bootsman
 * @link   http://www.nostromo.nl/wordpress-plugins/auto-hide-admin-bar/
 *
 * @param none
 *
 * @return none
 */
function ahab_plugin_setting_speed() {
	$options    = get_option( 'ahab_plugin_options' );
	$ahab_speed = DEFAULT_SPEED;

	if ( '' != sanitize_text_field( $options['speed'] ) ) {
		$ahab_speed = sanitize_text_field( $options['speed'] );
	}

	?>
	<input id='ahab_setting_speed' name='ahab_plugin_options[speed]' type='text' value='<?php echo $ahab_speed; ?>' />
<?php
}

/**
 * Output input field for delay
 *
 * @author Marcel Bootsman
 * @link   http://www.nostromo.nl/wordpress-plugins/auto-hide-admin-bar/
 *
 * @param none
 *
 * @return none
 */
function ahab_plugin_setting_delay() {
	$options    = get_option( 'ahab_plugin_options' );
	$ahab_delay = DEFAULT_DELAY;

	if ( '' != sanitize_text_field( $options['delay'] ) ) {
		$ahab_delay = sanitize_text_field( $options['delay'] );
	}

	?>
	<input id='ahab_setting_delay' name='ahab_plugin_options[delay]' type='text' value='<?php echo $ahab_delay; ?>' />
<?php
}

/**
 * Output input field for interval
 *
 * @author Marcel Bootsman
 * @link   http://www.nostromo.nl/wordpress-plugins/auto-hide-admin-bar/
 *
 * @param none
 *
 * @return none
 */
function ahab_plugin_setting_interval() {
	$options       = get_option( 'ahab_plugin_options' );
	$ahab_interval = DEFAULT_INTERVAL;

	if ( '' != sanitize_text_field( $options['interval'] ) ) {
		$ahab_interval = sanitize_text_field( $options['interval'] );
	}
	?>
	<input id='ahab_setting_interval' name='ahab_plugin_options[interval]' type='text'
	       value='<?php echo $ahab_interval; ?>' />
<?php
}

/**
 * Output radio buttons for showing Toolbar on small screens
 *
 * @author Marcel Bootsman
 * @link   http://www.nostromo.nl/wordpress-plugins/auto-hide-admin-bar/
 *
 * @param none
 *
 * @return none
 */
function ahab_plugin_setting_mobile() {
	$options = get_option( 'ahab_plugin_options' );

	?>

	<input type="radio" id="ahab_setting_mobile" name="ahab_plugin_options[mobile]"
	       value="1"  . <?php checked( 1, $options['mobile'], true ) . ' />'; ?>
    <label for="ahab_setting_mobile"><?php _e( 'Hide the Toolbar', 'auto-hide-admin-bar' ); ?></label>
    <input type="radio" id="ahab_setting_mobile" name="ahab_plugin_options[mobile]"
           value="2" . <?php checked( 2, $options['mobile'], true ) . '/>'; ?>
    <label for="ahab_setting_mobile"><?php _e( 'Always show the Toolbar', 'auto-hide-admin-bar' ); ?></label>

<?php
}

/**
 * Output radio buttons for showing Toolbar on small screens
 *
 * @author Marcel Bootsman
 * @link   http://www.nostromo.nl/wordpress-plugins/auto-hide-admin-bar/
 *
 * @param none
 *
 * @return none
 */
function ahab_plugin_setting_admin() {
	$options    = get_option( 'ahab_plugin_options' );
	$ahab_admin = DEFAULT_ADMIN;

	if ( '' != $options['admin'] ) {
		$ahab_admin = $options['admin'];
	}
	?>

	<input type="radio" id="ahab_setting_admin" name="ahab_plugin_options[admin]"
	       value="1"  . <?php checked( 1, $ahab_admin, true ) . ' />'; ?>
    <label for="ahab_setting_admin"><?php _e( 'Hide the Toolbar', 'auto-hide-admin-bar' ); ?></label>
    <input type="radio" id="ahab_setting_admin" name="ahab_plugin_options[admin]"
           value="2" . <?php checked( 2, $ahab_admin, true ) . '/>'; ?>
    <label for="ahab_setting_admin"><?php _e( 'Always show the Toolbar', 'auto-hide-admin-bar' ); ?></label>

<?php
}

/**
 * Validate the user input
 *
 * @author Marcel Bootsman
 * @link   http://www.nostromo.nl/wordpress-plugins/auto-hide-admin-bar/
 *
 * @param array $input Existing input fields
 *
 * @return array Sanitized input fields
 */
function ahab_validate_input( $input ) {
	// Create our array for storing the validated options
	$output = array();
	// Loop through each of the incoming options
	foreach ( $input as $key => $value ) {
		// Check to see if the current option has a value. If so, process it.
		if ( isset( $input[$key] ) ) {
			// Strip all HTML and PHP tags and properly handle quoted strings
			$output[$key] = strip_tags( stripslashes( $input[$key] ) );
		} // end if
	} // end foreach
	// Return the array processing any additional functions filtered by this action
	return apply_filters( 'ahab_validate_input', $output, $input );
}

?>
