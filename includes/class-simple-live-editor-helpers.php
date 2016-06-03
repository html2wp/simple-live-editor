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
	 * @return string The asset path for the file
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

	/**
	 * Find the first occurence of a string and replace it
	 * Splits $haystack into an array of 2 items by $needle, and then joins the array with $replace_with
	 * @see http://stackoverflow.com/a/1252717/3073849
	 * @param  string $haystack     The subject of the replacing
	 * @param  string $needle       The string to look for
	 * @param  string $replace_with The replacement string
	 * @return string The modified string
	 */
	public static function replace_first_occurrence( $haystack, $needle, $replace_with ) {
		return implode( $replace_with, explode( $needle, $haystack, 2 ) );
	}

	/**
	 * Recursive glob function
	 * @see http://stackoverflow.com/a/12109100/3073849
	 * @see http://php.net/manual/en/function.glob.php
	 * @param  string  $pattern The pattern. No tilde expansion or parameter substitution is done.
	 * @param  integer $flags   The glob flags. Note: Does not support flag GLOB_BRACE
	 * @return mixed   Returns an array containing the matched files/directories, an empty array if no file matched or FALSE on error.
	 */
	public static function glob_recursive( $pattern, $flags = 0 ) {

		$files = glob( $pattern, $flags );

		foreach ( glob( dirname( $pattern ) . '/*', GLOB_ONLYDIR | GLOB_NOSORT ) as $dir ) {
			$files = array_merge( $files, self::glob_recursive( $dir . '/' . basename( $pattern ), $flags ) );
		}

		return $files;
	}

	/**
	 * Get human readable title from slug
	 * @param  string $slug The slug
	 * @return string The human readable title
	 */
	public static function get_title_from_slug( $slug ) {
		return ucwords( str_replace( '-', ' ', $slug ) );
	}

	/**
	 * Get path to the sections directory
	 * @return string Path to the sections directory
	 */
	public static function get_sections_directory() {
		return get_stylesheet_directory() . '/simple-live-editor/sections/';
	}
}