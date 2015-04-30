<?php

/**
 * Plugin Name: Disable Email In Non-Production Environments
 * Description: Disable sending email in non-production environments, except for password reset emails.
 * Version: 1.0
 * Author: Marty Penner
 */

if (!function_exists('wp_mail') && getenv('WP_ENV') !== 'production') {
    /**
     * Fake sending an email.
     *
     * @param string|array $to
     * @param string       $subject
     * @param string       $message
     * @param string       $headers
     * @param array        $attachments
     *
     * @return bool
     */
    function wp_mail($to, $subject, $message, $headers = '', $attachments = [])
    {
        if (false !== stripos($subject, 'Password Reset')) {
            $sendSuccessful = wpMandrill::mail($to, $subject, $message, $headers, $attachments);

            return $sendSuccessful;
        }

        return true;
    }
}
