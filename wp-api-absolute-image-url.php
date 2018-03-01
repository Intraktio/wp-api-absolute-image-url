<?php
/*
Plugin Name: WP API Absolute Image Url
*/

function contains_string($string, $substring) {
    return strpos($string, $substring)===false;
}

function append_site_url_to_srcset($sources, $size_array, $image_src, $image_meta, $attachment_id) {
    $base_url = get_site_url();
    foreach ($sources as &$source) {
        $url = $source["url"];
        if(contains_string($url, $base_url)){
            $source["url"] = $base_url . $url;
        }
    }
    return $sources;
}

function add_filters_to_api_requests( $query ) {
    try {
        $is_rest = array_key_exists('rest_route', $query->query_vars);
        if( $is_rest === true ) {
            add_filter("wp_calculate_image_srcset", "append_site_url_to_srcset", 10, 5);
        }
    } catch (Exception $e) {
    }
    return $query;
}

add_action('parse_request', 'add_filters_to_api_requests', 3, 1);