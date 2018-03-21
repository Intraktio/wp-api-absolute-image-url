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
        add_action( 'parse_request', array($this, 'add_filters_to_api_requests'), 3, 1 );
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
                add_filter( 'wp_calculate_image_srcset', array($this, 'append_site_url_to_srcset'), 10, 5 );
	        add_filter( 'wp_get_attachment_image_src', array( $this, 'handle_attachment_image_urls' ), 10, 4 );
                add_filter( 'wp_get_attachment_url', array( $this, 'handle_attachment_url' ), 10, 2 );
            }
        } catch (Exception $e) {}
        return $query;
    }

    /**
     * Converts relative attachment image urls to absolute.
     *
     * @param array|false  $image         Either array with src, width & height, icon src, or false.
     * @param int          $attachment_id Image attachment ID.
     * @param string|array $size          Size of image. Image size or array of width and height values
     *                                    (in that order). Default 'thumbnail'.
     * @param bool         $icon          Whether the image should be treated as an icon. Default false.
     */
    public function handle_attachment_image_urls( $image, $attachment_id, $size, $icon ) {
            if ( $image === false ) {
                    return $image;
            }
            $image[0] = $this->get_absolute_url( $image[0] );
            return $image;
    }

    /**
     * Convverts relative attachment url to absolute;
     *
     * @param string $url           URL for the given attachment.
     * @param int    $attachment_id Attachment post ID.
     */
    public function handle_attachment_url( $url, $attachment_id ) {
        return $this->get_absolute_url( $url );
    }

    /**
     * Converts relative url to absolute.
     * @param string    $url    Url to convert.
     */
    private function get_absolute_url( $url ) {
            if ( !preg_match("/^[a-zA-Z]+:\/\//", $url) ) {
                    return get_site_url() . $url;
            }
            return $url;
    }

}

(new WP_API_Absolute_Image_Url())->init();
