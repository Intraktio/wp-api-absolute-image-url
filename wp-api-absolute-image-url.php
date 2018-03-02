<?php
/*
Plugin Name:    WP API Absolute Image Url
Plugin URI:     https://github.com/Intraktio/wp-api-absolute-image-url
Description:    Transform srcset urls to absolute urls when fetching content over WP API
Version:        20180302
Author:         Intraktio Ltd
Author URI:     https://intraktio.com
License:        MIT
Domain Path:    /languages
*/

class WP_API_Absolute_Image_Url {

    function init() {
        add_action('parse_request', array($this, 'add_filters_to_api_requests'), 3, 1);
    }

    function contains_string($string, $substring) {
        return strpos($string, $substring)!==false;
    }

    function append_site_url_to_srcset($sources, $size_array, $image_src, $image_meta, $attachment_id) {
        $base_url = get_site_url();
        foreach ($sources as &$source) {
            $url = $source['url'];
            if(!$this->contains_string($url, $base_url)){
                $source['url'] = $base_url . $url;
            }
        }
        return $sources;
    }

    function add_filters_to_api_requests( $query ) {
        try {
            $is_rest = array_key_exists('rest_route', $query->query_vars);
            if( $is_rest === true ) {
                add_filter('wp_calculate_image_srcset', array($this, 'append_site_url_to_srcset'), 10, 5);
            }
        } catch (Exception $e) {}
        return $query;
    }

}

(new WP_API_Absolute_Image_Url())->init();
