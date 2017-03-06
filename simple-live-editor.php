<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://htmltowordpress.io/
 * @since             1.0.0
 * @package           Simple_Live_Editor
 *
 * @wordpress-plugin
 * Plugin Name:       Simple Live Editor
 * Plugin URI:        http://htmltowordpress.io/
 * Description:       Edit your content live from the WordPress customize view - content management made fast and easy.
 * Version:           2.1
 * Author:            htmltowordpress.io
 * Author URI:        http://htmltowordpress.io/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       simple-live-editor
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Load and run the plugin update checker
 */
require_once plugin_dir_path( __FILE__ ) . 'vendor/yahnis-elsts/plugin-update-checker/plugin-update-checker.php';

$sle_github_update_checker = PucFactory::getLatestClassVersion('PucGitHubChecker');
$sle_update_checker = new $sle_github_update_checker( 'https://github.com/html2wp/simple-live-editor/', __FILE__, 'master' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-simple-live-editor-activator.php
 */
function activate_simple_live_editor() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-simple-live-editor-activator.php';
	Simple_Live_Editor_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-simple-live-editor-deactivator.php
 */
function deactivate_simple_live_editor() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-simple-live-editor-deactivator.php';
	Simple_Live_Editor_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_simple_live_editor' );
register_deactivation_hook( __FILE__, 'deactivate_simple_live_editor' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-simple-live-editor.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_simple_live_editor() {

	$plugin = new Simple_Live_Editor();
	$plugin->run();

}
run_simple_live_editor();
