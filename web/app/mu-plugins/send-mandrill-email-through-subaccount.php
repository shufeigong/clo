<?php

namespace Tenzing\WordPress;

/**
 * Plugin Name: Send Mandrill Email Through Subaccount
 * Version: 1.0
 * Author: Marty Penner
 */

add_filter('mandrill_payload', __NAMESPACE__.'\sendEmailThroughMandrillSubaccount', 100);
/**
 * Send all email that Mandrill is handling through a subaccount.
 *
 * @param array $message
 *
 * @return array
 */
function sendEmailThroughMandrillSubaccount(array $message)
{
    $message['subaccount'] = getenv('CLO Web 2015');

    return $message;
}
