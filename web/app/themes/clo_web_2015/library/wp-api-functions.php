<?php
/**
 * Created by PhpStorm.
 * User: insu
 * Date: 15-08-30
 * Time: 4:04 PM
 */

function custom_json_api_prepare_post( $post_response, $post, $context ) {

    $isPage = $post['type'] == 'page';

    if($isPage) {
        $post_response['sub_menu'] = get_post_meta($post['ID']);

    }

    return $post_response;
}
add_filter( 'json_prepare_post', 'custom_json_api_prepare_post', 10, 3 );