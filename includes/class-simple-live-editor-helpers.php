<?php

/**
 * The static helper functions
 *
 * @link       http://htmltowordpress.io/
 * @since      1.0.0
 *
 * @package    Helpers
 * @author     Harri Heljala <harri@htmltowordpress.io>
 */
class Helpers {

    /**
     * Helper function taken from ACF 4.x to allow finding of asset paths when plugin is included from outside the plugins directory
     * @param  string $file The file for which the asset path will be returned for
     * @return string       The asset path for the file
     */
    public static function get_dir_url( $file ) {

        $dir = trailingslashit( dirname( $file ) );
        $count = 0;

        // sanitize for Win32 installs
        $dir = str_replace( '\\' ,'/', $dir );

        // if file is in plugins folder
        $wp_plugin_dir = str_replace( '\\' ,'/', WP_PLUGIN_DIR );
        $dir = str_replace( $wp_plugin_dir, plugins_url(), $dir, $count );

        // if file is in wp-content folder
        if ( $count < 1 ) {
            $wp_content_dir = str_replace( '\\' ,'/', WP_CONTENT_DIR );
            $dir = str_replace( $wp_content_dir, content_url(), $dir, $count );
        }

        // if file is in ??? folder
        if ( $count < 1 ) {
            $wp_dir = str_replace( '\\' ,'/', ABSPATH );
            $dir = str_replace( $wp_dir, site_url( '/' ), $dir );
        }

        return $dir;

    }

}