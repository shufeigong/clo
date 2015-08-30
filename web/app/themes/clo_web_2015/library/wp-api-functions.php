<?php
/**
 * Created by PhpStorm.
 * User: insu
 * Date: 15-08-30
 * Time: 4:04 PM
 */

function custom_json_api_prepare_post( $post_response, $post, $context ) {

    $meta = get_post_meta( $post['ID'] );
    $post_response['meta_field_name'] = $meta;

    return $post_response;
}
add_filter( 'json_prepare_post', 'custom_json_api_prepare_post', 10, 3 );