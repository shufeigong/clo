<?php

/**
 * Plugin Name: Disable Email In Non-Production Environments
 * Description: Disable sending email in non-production environments.
 * Version: 1.0
 * Author: Marty Penner
 */

if (!function_exists('wp_mail') && getenv('WP_ENV') !== 'production') {
    /**
     * Fake sending an email.
     *
     * @return bool
     */
    function wp_mail(/* $to, $subject, $message, $headers = '', $attachments = [] */)
    {
        return true;
    }
}
