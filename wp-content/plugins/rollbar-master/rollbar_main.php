<?php
/*
Plugin Name: Rollbar for WordPress
Plugin URI: https://github.com/digitalmedia/rollbar
Description: Rollbar collects and analyzes errors so you can find and fix them faster.
Version: 1.0.3
Author: Sully Syed
Author URI: http://yllus.com/
*/
require_once 'Rollbar.class.php';

define('ROLLBAR_DEFAULT_ENVIRONMENT', 'production');

// Initialize Rollbar using the class file defined above.
function rollbar_initialize() {
    // If logging is not enabled, exit immediately.
    $str_is_logging_enabled = rollbar_is_logging_enabled();
    if ( $str_is_logging_enabled == false ) {
        return;
    }

    $str_access_token = rollbar_get_access_token();
    if ( $str_access_token == '' ) {
        return;
    }
    $str_environment = rollbar_get_environment();

    $int_logging_level = get_option('rollbar_logging_level', '1024');

    if ( strlen($str_access_token) > 0 ) {
        Rollbar::init(
            array(
                'access_token' => $str_access_token, // required.
                'environment' => $str_environment, // optional - environment name. any string will do.
                'root' => ABSPATH, // optional - dir your code is in. used for linking stack traces.
                'max_errno' => $int_logging_level // optional - max error number to report. defaults to 1024 (ignore E_STRICT and above)
            )
        );
    }
}
add_action('init', 'rollbar_initialize');

// Inject <script> tags into the header of all pages on the site to log JavaScript errors.
function rollbar_wp_head() {
    // If logging is not enabled, exit immediately.
    $str_is_logging_enabled = rollbar_is_logging_enabled();
    if ( $str_is_logging_enabled == false ) {
        return;
    }

    $str_client_access_token = rollbar_get_client_access_token();
    if ( $str_client_access_token == '' ) {
        return;
    }
    $str_environment = rollbar_get_environment();

    ?>
    <script>
    var _rollbarParams = {"server.environment": "<?php echo $str_environment; ?>"};
    _rollbarParams["notifier.snippet_version"] = "2"; var _rollbar=["<?php echo $str_client_access_token; ?>", _rollbarParams]; var _ratchet=_rollbar;
    (function(w,d){w.onerror=function(e,u,l){_rollbar.push({_t:'uncaught',e:e,u:u,l:l});};var i=function(){var s=d.createElement("script");var 
    f=d.getElementsByTagName("script")[0];s.src="//d37gvrvc0wt4s1.cloudfront.net/js/1/rollbar.min.js";s.async=!0;
    f.parentNode.insertBefore(s,f);};if(w.addEventListener){w.addEventListener("load",i,!1);}else{w.attachEvent("onload",i);}})(window,document);
    </script>
    <?php
}
add_action('wp_head', 'rollbar_wp_head', 3);

// Add "Rollbar" to the Settings menu.
function rollbar_add_settings_page() {
    add_options_page('Rollbar - Settings', 'Rollbar', 'manage_options', 'rollbar_settings', 'rollbar_settings_do_page');
}
add_action('admin_menu', 'rollbar_add_settings_page');

// Output the fields and text of the shortcodes page.
function rollbar_settings_do_page() {
    if ( !current_user_can( 'manage_options' ) )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }

    ?>
    <div class="wrap">
        <div id="icon-options-general" class="icon32">
            <br />
        </div>
        <form method="post" action="options.php">    
            <h2>Rollbar</h2>
            
            <?php do_settings_sections('rollbar_settings_page'); ?>
            
            <p class="submit">
                <input name='submit' type='submit' id='submit' class='button-primary' value='<?php _e("Save Changes") ?>' />
            </p>
            
            <?php settings_fields('rollbar_settings_group'); ?>
        </form>        
    </div>
    <?php
}

// Whitelist the settings.
add_action('admin_init', 'rollbar_init');
function rollbar_init() {
    // Add the "Rollbar Setting Section" settings section (heading not shown).
    add_settings_section('rollbar_settings_section', '', 'rollbar_settings_section_text', 'rollbar_settings_page');

    // Add the "Enable Logging of PHP Errors?" field and register it.
    add_settings_field('rollbar_enable_logging', 'Enable Logging of PHP Errors?', 'rollbar_enable_logging_text', 'rollbar_settings_page', 'rollbar_settings_section');
    register_setting('rollbar_settings_group', 'rollbar_enable_logging');

    // Add the "Enable Logging of JavaScript Errors?" field and register it.
    add_settings_field('rollbar_enable_javascript_logging', 'Enable Logging of JavaScript Errors?', 'rollbar_enable_javascript_logging_text', 'rollbar_settings_page', 'rollbar_settings_section');
    register_setting('rollbar_settings_group', 'rollbar_enable_javascript_logging');

    // Add the "Error Logging Level" and register it.
    add_settings_field('rollbar_logging_level', 'Error Logging Level', 'rollbar_set_logging_level_text', 'rollbar_settings_page', 'rollbar_settings_section');
    register_setting('rollbar_settings_group', 'rollbar_logging_level');

    // Add the "Access Token (Client)" field and register it.
    add_settings_field('rollbar_access_token_client', 'Access Token (Client)', 'rollbar_client_access_token_text', 'rollbar_settings_page', 'rollbar_settings_section');
    register_setting('rollbar_settings_group', 'rollbar_access_token_client');
    
    // Add the "Access Token (Server)" field and register it.
    add_settings_field('rollbar_access_token', 'Access Token (Server)', 'rollbar_access_token_text', 'rollbar_settings_page', 'rollbar_settings_section');
    register_setting('rollbar_settings_group', 'rollbar_access_token');

    // Add the "Environment" field and register it.
    add_settings_field('rollbar_environment', 'Environment', 'rollbar_environment_text', 'rollbar_settings_page', 'rollbar_settings_section');
    register_setting('rollbar_settings_group', 'rollbar_environment');
}

function rollbar_settings_section_text() {
    1;
}

function rollbar_enable_logging_text() {
    $current_val = get_option('rollbar_enable_logging', '1');
    
    $is_selected = '';
    if ( $current_val == '0' ) {
        $is_selected = ' selected="selected"';
    }
    ?>
    <select name="rollbar_enable_logging">
        <option value="1">Yes</option>
        <option value="0"<?php echo $is_selected; ?>>No</option>
    </select>
    <?php
}

function rollbar_enable_javascript_logging_text() {
    $current_val = get_option('rollbar_enable_javascript_logging', '1');
    
    $is_selected = '';
    if ( $current_val == '0' ) {
        $is_selected = ' selected="selected"';
    }
    ?>
    <select name="rollbar_enable_javascript_logging">
        <option value="1">Yes</option>
        <option value="0"<?php echo $is_selected; ?>>No</option>
    </select>
    <?php
}

function rollbar_set_logging_level_text() {
    $current_val = get_option('rollbar_logging_level', '1024');
    ?>
    <select name="rollbar_logging_level">
        <option value="1" <?php echo rollbar_get_error_level_match($current_val, 1); ?>>Fatal run-time errors (E_ERROR) only</option>
        <option value="2" <?php echo rollbar_get_error_level_match($current_val, 2); ?>>Run-time warnings (E_WARNING) and above</option>
        <option value="4" <?php echo rollbar_get_error_level_match($current_val, 4); ?>>Compile-time parse errors (E_PARSE) and above</option>
        <option value="8" <?php echo rollbar_get_error_level_match($current_val, 8); ?>>Run-time notices (E_NOTICE) and above</option>
        <option value="256" <?php echo rollbar_get_error_level_match($current_val, 256); ?>>User-generated error messages (E_USER_ERROR) and above</option>
        <option value="512" <?php echo rollbar_get_error_level_match($current_val, 512); ?>>User-generated warning messages (E_USER_WARNING) and above</option>
        <option value="1024" <?php echo rollbar_get_error_level_match($current_val, 1024); ?>>User-generated notice messages (E_USER_NOTICE) and above</option>
        <option value="2048" <?php echo rollbar_get_error_level_match($current_val, 2028); ?>>Suggest code changes to ensure forward compatibility (E_STRICT) and above</option>
        <option value="8192" <?php echo rollbar_get_error_level_match($current_val, 8192); ?>>Warnings about code that will not work in future versions (E_DEPRECATED) and above</option>
        <option value="32767" <?php echo rollbar_get_error_level_match($current_val, 32767); ?>>Absolutely everything (E_ALL)</option>
    </select>
    <?php
}

function rollbar_access_token_text() {
    $current_val = rollbar_get_access_token();
    ?>
    <input type="text" name="rollbar_access_token" value="<?php echo $current_val; ?>" style="width: 300px;" />
    <p class="description">Please provide your project's <b>post_server_item</b> access token. This field is mandatory for Rollbar to function.</p>
    <?php
}

function rollbar_client_access_token_text() {
    $current_val = rollbar_get_client_access_token();
    ?>
    <input type="text" name="rollbar_access_token_client" value="<?php echo $current_val; ?>" style="width: 300px;" />
    <p class="description">Please provide your project's <b>post_client_item</b> access token. This field is mandatory for Rollbar to function.</p>
    <?php
}

function rollbar_environment_text() {
    $current_val = '';
    $str_disabled = '';
    $str_disabled_class = '';
    $str_disabled_explain = '';
    if ( defined('ROLLBAR_THIS_ENVIRONMENT') ) {
        $current_val = ROLLBAR_THIS_ENVIRONMENT;
        $str_disabled = 'disabled="disabled" ';
        $str_disabled_class = ' background-color: #f0f0f0;';
        $str_disabled_explain = ' <i>(<b>ROLLBAR_THIS_ENVIRONMENT</b> has been hardcoded to this value)</i>';
    }
    else {
        $current_val = get_option('rollbar_environment');
        if ( is_null($current_val) == true ) {
            $current_val = ROLLBAR_DEFAULT_ENVIRONMENT;
        }
        if ( strlen($current_val) == 0 ) {
            $current_val = ROLLBAR_DEFAULT_ENVIRONMENT;
        }
    }
    ?>
    <input type="text" name="rollbar_environment" value="<?php echo $current_val; ?>" style="width: 300px;<?php echo $str_disabled_class; ?>" <?php echo $str_disabled; ?>/><?php echo $str_disabled_explain; ?>
    <p class="description">This setting is optional, but will allow you to distinguish between separate warnings and errors received in your local, development and production systems. (If left blank, this value will default to <b>production</b>.)</p>
    <?php
}

function rollbar_is_logging_enabled() {
    $str_is_logging_enabled = get_option('rollbar_enable_logging', '1');

    if ( $str_is_logging_enabled == '1' ) {
        return true;
    }

    return false;
}

function rollbar_get_access_token() {
    $str_access_token = get_option('rollbar_access_token');
    if ( is_null($str_access_token) == true ) {
        $str_access_token = '';
    }  

    return $str_access_token;
}

function rollbar_get_client_access_token() {
    $str_access_token = get_option('rollbar_access_token_client');
    if ( is_null($str_access_token) == true ) {
        $str_access_token = '';
    }  

    return $str_access_token;
}

function rollbar_get_environment() {
    $str_environment = '';
    if ( defined('ROLLBAR_THIS_ENVIRONMENT') ) {
        $str_environment = ROLLBAR_THIS_ENVIRONMENT;
    }
    else {
        $str_environment = get_option('rollbar_environment');
        if ( is_null($str_environment) == true ) {
            $str_environment = ROLLBAR_DEFAULT_ENVIRONMENT;
        }
        if ( strlen($str_environment) == 0 ) {
            $str_environment = ROLLBAR_DEFAULT_ENVIRONMENT;
        }
    }

    return $str_environment;
}

function rollbar_get_error_level_match( $current_val, $error_level ) {
    if ( $current_val == $error_level ) {
        return 'selected="selected"';
    }
    else {
        return '';
    }
}
?>