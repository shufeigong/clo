<?php
/*
Plugin Name: Auto Hide Admin Bar
Plugin URI: https://www.nostromo.nl/wordpress-plugins/auto-hide-admin-bar
Description: Automatically hides the Toolbar. Will show the Toolbar when hovering over the top of the site.
Author: Marcel Bootsman
Version: 0.9.1
Author URI: https://www.nostromo.nl
*/

/* ----------------------------------------------------------------------------
 *  Global data */
$plugin_file = dirname( __FILE__ ) . '/auto-hide-admin-bar.php';
$plugin_path = plugin_dir_path( $plugin_file );

/* Define some default numbers */
define( 'DEFAULT_SPEED', 200 );
define( 'DEFAULT_DELAY', 1500 );
define( 'DEFAULT_INTERVAL', 100 );
define( 'DEFAULT_MOBILE', 1 );
define( 'DEFAULT_ADMIN', 2 );

/**
 * Returns current plugin version.
 *
 * @return string Plugin version
 */
function plugin_get_version() {
	$plugin_data    = get_plugin_data( __FILE__ );
	$plugin_version = $plugin_data[ 'Version' ];

	return $plugin_version;
}

/**
 * Include options page for admin area
 *
 */
if ( is_admin() ) {
	include_once $plugin_path . 'ahab_options.php';
}

/**
 * Add Settings link to plugin page
 *
 * @author Marcel Bootsman
 * @link   http://www.nostromo.nl/wordpress-plugins/auto-hide-admin-bar/
 *
 * @param Array $links , filename $file
 *
 * @return Array $links with new link=
 */
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'ahab_add_settings_link' );
function ahab_add_settings_link( $links ) {

	$ahab_links = array( '<a href="options-general.php?page=auto-hide-admin-bar">' . __( "Settings", "auto-hide-admin-bar" ) . '</a>' );

	return array_merge( $links, $ahab_links );
}

/**
 * The main function. Build JS code and output it.
 *
 * @author Marcel Bootsman
 * @link   http://www.nostromo.nl/wordpress-plugins/auto-hide-admin-bar/
 *
 * @param None
 *
 * @return None
 */

function auto_hide_admin_bar() {
	/* Get options
	 *
	 */
	$options = get_option( 'ahab_plugin_options' );

	if ( ( $options[ 'speed' ] != '' ) && ( is_numeric( $options[ 'speed' ] ) ) ) {
		$ahab_anim_speed = $options[ 'speed' ];
	} else {
		$ahab_anim_speed = DEFAULT_SPEED;
	}

	if ( ( $options[ 'delay' ] != '' ) && ( is_numeric( $options[ 'delay' ] ) ) ) {
		$ahab_delay = $options[ 'delay' ];
	} else {
		$ahab_delay = DEFAULT_DELAY;
	}

	if ( ( $options[ 'interval' ] != '' ) && ( is_numeric( $options[ 'interval' ] ) ) ) {
		$ahab_interval = $options[ 'interval' ];
	} else {
		$ahab_interval = DEFAULT_INTERVAL;
	}

	if ( ( $options[ 'mobile' ] != '' ) && ( is_numeric( $options[ 'mobile' ] ) ) ) {
		$ahab_mobile = $options[ 'mobile' ];
	} else {
		$ahab_mobile = DEFAULT_MOBILE;
	}

	/**
	 * Theme name check - For now only for Twenty Fourteen
	 * because of the fixed header/menu
	 **/
	if ( function_exists( 'wp_get_theme' ) ) {
		$theme_name = ( wp_get_theme()->Template );
	};

	?>

	<script type='text/javascript'>
		jQuery(document).ready(function ($) {

			function ahadMain() {
				// check if page is in iframe & user is logged in - if so, customizer is active
				var isInIframe = (window.location != window.parent.location) ? true : false;

				if (!isInIframe && ($('body').hasClass('logged-in'))) {
					var themeName = '<?php echo $theme_name; ?>';
					var windowSize = $(window).width();
					var ahabMobile = <?php echo $ahab_mobile; ?>;
					if (windowSize > 782) {
						$('#wpadminbar').css('top', '-32px');
						$('body').css('margin-top', '-32px');
						if ('twentyfourteen' == themeName) {
							$('.admin-bar.masthead-fixed .site-header').css('top', '0px');
						}
					}
					else {
						if (1 == ahabMobile) {
							$('#wpadminbar').css('z-index', '99999 !important');
							$('#wpadminbar').css('cssText', 'z-index: 99999 !important; top: -46px;');
							$('body').css('margin-top', '-46px');
						}
						else {
							$('#wpadminbar').css('top', '0px');
							$('body').css('margin-top', '0px');

						}

					}

					if ($('#hiddendiv').length == 0) {
						$('body').append('<div id=\'hiddendiv\'></div>');
					}

					$autoHide = $(this).find('#hiddendiv');
					$autoHide.css('width', '100%');
					if ((windowSize < 782) && (1 == ahabMobile)) {
						$autoHide.css('min-height', '46px');
					}
					else {
						$autoHide.css('min-height', '32px');
					}
					$autoHide.css('z-index', '99998'); // admin bar is at z-index: 99999;
					$autoHide.css('position', 'fixed');
					$autoHide.css('top', '0px');

					var configIn = {
						over       : adminBarIn, // function = onMouseOver callback (REQUIRED)
						sensitivity: 6,
						out        : doNothing // function = onMouseOut callback (REQUIRED)
					};
					var configOut = {
						over    : doNothing, // function = onMouseOver callback (REQUIRED)
						timeout : <?php echo $ahab_delay; ?>, // number = milliseconds delay before onMouseOut
						interval: <?php echo $ahab_interval; ?>, // number = millseconds interval for mouse polling
						out     : adminBarOut // function = onMouseOut callback (REQUIRED)
					};

					$autoHide.hoverIntent(configIn);
					$('#wpadminbar').hoverIntent(configOut);

					// doNothing function is for enabling hoverIntent to work with two layers.
					function doNothing() {
					}

					// Show the Admin Bar
					function adminBarIn() {
						$('#wpadminbar').animate({'top': '0px'}, <?php echo $ahab_anim_speed; ?>);
						$('body').animate({'margin-top': '0px'}, <?php echo $ahab_anim_speed; ?>);
						$('body').animate({'background-position-y': '0px'}, <?php echo $ahab_anim_speed; ?>);
						if ('twentyfourteen' == themeName) {
							$('.admin-bar.masthead-fixed .site-header').animate({'top': '32px'}, <?php echo $ahab_anim_speed; ?>)
						}
					}

					// Hide the Admin Bar
					function adminBarOut() {
						if (windowSize > 782) {
							$('#wpadminbar').animate({'top': '-32px'}, <?php echo $ahab_anim_speed; ?>);
							$('body').animate({'margin-top': '-32px'}, <?php echo $ahab_anim_speed; ?>);
							$('body').animate({'background-position-y': '-32px'}, <?php echo $ahab_anim_speed; ?>);
							if ('twentyfourteen' == themeName) {
								$('.admin-bar.masthead-fixed .site-header').animate({'top': '0px'}, <?php echo $ahab_anim_speed; ?>)
							}
						}
						else {
							if (1 == ahabMobile) {
								$('#wpadminbar').animate({'top': '-46px'}, <?php echo $ahab_anim_speed; ?>);
								$('body').animate({'margin-top': '-46px'}, <?php echo $ahab_anim_speed; ?>);
								$('body').animate({'background-position-y': '-46px'}, <?php echo $ahab_anim_speed; ?>);
								if ('twentyfourteen' == themeName) {
									$('.admin-bar.masthead-fixed .site-header').animate({'top': '-46px'}, <?php echo $ahab_anim_speed; ?>)
								}
							}
						}

					}
				}
			}

			$(document).ready(ahadMain);
			$(window).on('resize', ahadMain);

		});

	</script>
<?php
}

/**
 * Add jQuery
 *
 * @author Marcel Bootsman
 * @link   http://www.nostromo.nl/wordpress-plugins/auto-hide-admin-bar/
 *
 * @param None
 *
 * @return None
 */

add_action( 'wp_footer', 'ahab_add_jquery_stuff' );
function ahab_add_jquery_stuff() {
	if ( is_user_logged_in() ) {
		/* determine plugin path */
		$x = WP_PLUGIN_URL . '/' . str_replace( basename( __FILE__ ), "", plugin_basename( __FILE__ ) );

		wp_enqueue_script( 'jquery' );
		wp_register_script( 'jquery-hoverintent', $x . 'js/jquery.hoverIntent.minified.js' );
		wp_enqueue_script( 'jquery-hoverintent' );
	}
}

/**
 * Hook main function for logged in users
 *
 * @author Marcel Bootsman
 * @link   http://www.nostromo.nl/wordpress-plugins/auto-hide-admin-bar/
 *
 * @param None
 *
 * @return None
 */
add_action( 'wp_footer', 'ahab_add_my_hide_stuff' );
function ahab_add_my_hide_stuff() {
	if ( is_user_logged_in() ) {
		auto_hide_admin_bar();
	}
}

/**
 * Hook main function in admin screens
 *
 * @author Marcel Bootsman
 * @link   http://www.nostromo.nl/wordpress-plugins/auto-hide-admin-bar/
 *
 * @param None
 *
 * @return None
 */
add_action( 'admin_footer', 'ahab_admin_add_my_hide_stuff' );
function ahab_admin_add_my_hide_stuff() {
	$options = get_option( 'ahab_plugin_options' );
	if ( ( '' != $options[ 'admin' ] ) && ( is_numeric( $options[ 'admin' ] ) ) ) {
		$ahab_admin = $options[ 'admin' ];
	} else {
		$ahab_admin = DEFAULT_ADMIN;
	}

	if ( 1 == $ahab_admin ) {

		auto_hide_admin_bar();
	}

}

?>