<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://htmltowordpress.io/
 * @since      1.0.0
 *
 * @package    Simple_Live_Editor
 * @subpackage Simple_Live_Editor/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Simple_Live_Editor
 * @subpackage Simple_Live_Editor/includes
 * @author     Harri Heljala <harri@htmltowordpress.io>
 */
class Simple_Live_Editor {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Simple_Live_Editor_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * The admin instance.
	 *
	 * @since    1.0.1
	 * @access   protected
	 * @var      Simple_Live_Editor_Admin    $plugin_admin    The admin instance.
	 */
	protected $plugin_admin;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'simple-live-editor';
		$this->version = '1.0.1';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_editor_hooks();
		$this->define_constants();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - The Composer autoload includes
	 * - Simple_Live_Editor_Loader. Orchestrates the hooks of the plugin.
	 * - Simple_Live_Editor_i18n. Defines internationalization functionality.
	 * - Simple_Live_Editor_Admin. Defines all hooks for the admin area.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The Composer autoload includes
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'vendor/autoload.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-simple-live-editor-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-simple-live-editor-i18n.php';

		/**
		 * The static helper functions
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-simple-live-editor-helpers.php';

		/**
		 * The customizer section class
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-customize-control-sle-section.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-simple-live-editor-admin.php';

		$this->loader = new Simple_Live_Editor_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Simple_Live_Editor_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Simple_Live_Editor_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		/**
		 * Init
		 */
		$this->plugin_admin = new Simple_Live_Editor_Admin( $this->get_plugin_name(), $this->get_version() );

		/**
		 * Customize controls
		 */
		$this->loader->add_action( 'customize_controls_init', $this->plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'customize_register', $this->plugin_admin, 'add_customize_controls' );

		/**
		 * Customize preview
		 * These hooks are actually in the public scope for them to work in the customize view
		 * The customize view check needs to be done in the callback
		 */
		$this->loader->add_action( 'wp_enqueue_scripts', $this->plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $this->plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'template_include', $this->plugin_admin, 'serve_template' );
		$this->loader->add_action( 'wp_footer', $this->plugin_admin, 'add_editor_modal' );
		$this->loader->add_action( 'wp_footer', $this->plugin_admin, 'add_link_edit_modal' );

		/**
		 * Ajax
		 */
		$this->loader->add_action( 'wp_ajax_sle_get_content', $this->plugin_admin, 'get_content' );
		$this->loader->add_action( 'wp_ajax_sle_save_content', $this->plugin_admin, 'save_content' );

		/**
		 * Duplicate an existing template when translated
		 */
		$this->loader->add_action( 'save_post', $this->plugin_admin, 'duplicate_template_on_translation', 999 );

	}

	/**
	 * Define hooks required for showing the notifications in the wp-admin Page editing screen
	 * to let users know that they should use the Customize view instead
	 * in order to edit the content very easily using the Simple Live Editor plugin
	 *
	 * @since    1.0.1
	 * @access   private
	 */
	private function define_editor_hooks() {

		global $pagenow, $typenow;

		if ( empty( $typenow ) ) {

			// Try to pick the current screen from the query string
			if ( ! empty( $_GET['post_type'] ) ) {
				$typenow = strtolower( $_GET['post_type'] );
			}
			// try to the current screen from the post id
			elseif ( ! empty( $_GET['post'] ) ) {
				$post = get_post( $_GET['post'] );
				$typenow = $post->post_type;
			}
		}

		// Show the SLE notification only for add new page and edit existing page
		if ( ( $pagenow === 'post.php' || $pagenow === 'post-new.php') && $typenow === 'page' ) {
			$this->loader->add_action( 'admin_enqueue_scripts', $this->plugin_admin, 'enqueue_sle_notice_styles' );
			$this->loader->add_action( 'admin_notices', $this->plugin_admin, 'show_sle_notice' );
		}
	}

	/**
	 * Define contants used by the plugin
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_constants() {

		define( 'SLE_PHRASING_CONTENT', 'a, abbr, map, area, audio, b, bdi, bdo, br, button, canvas, cite, code, data, datalist, del, dfn, em, embed, i, iframe, img, input, ins, kbd, keygen, label, map, mark, math, meter, noscript, object, output, progress, q, ruby, s, samp, script, select, small, span, strong, sub, sup, svg, template, textarea, time, u, var, video, wbr, text' );

		define( 'SLE_HEADING_CONTENT', 'h1, h2, h3, h4, h5, h6' );

		define( 'SLE_EDITABLE_ELEMENTS', SLE_PHRASING_CONTENT . ', ' . SLE_HEADING_CONTENT . ', p, ul, ol, li' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Simple_Live_Editor_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}