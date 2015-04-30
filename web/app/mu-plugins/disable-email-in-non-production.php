<?php

/**
 * Plugin Name: Disable Email In Non-Production Environments
 * Version: 1.0
 * Author: Marty Penner
 */

if (!function_exists('wp_mail') && getenv('WP_ENV') !== 'production') {
    /**
     * Fake sending an email.
     *
     * @return bool
     */
    function wp_mail()
    {
        return true;
    }
}
