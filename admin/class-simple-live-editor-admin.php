<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://helja.la/
 * @since      1.0.0
 *
 * @package    Simple_Live_Editor
 * @subpackage Simple_Live_Editor/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Simple_Live_Editor
 * @subpackage Simple_Live_Editor/admin
 * @author     Harri Heljala <harri@htmltowordpress.io>
 */
class Simple_Live_Editor_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Simple_Live_Editor_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Simple_Live_Editor_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/simple-live-editor-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Simple_Live_Editor_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Simple_Live_Editor_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/simple-live-editor-admin.js', array( 'jquery' ), $this->version, false );

	}


	public function prepare_template_for_editing( $template ) {

		$dom = phpQuery::newDocumentFilePHP( $template );

		/**
		 * Create our indexing for all HTML elements
		 */
		foreach ( $dom->find( '*:not(php)' ) as $key => $el ) {
			pq( $el )->attr( 'data-sle-dom-index', $key );
		}

		/**
		 * Find all text nodes and mark their parents as editable elements
		 */
		foreach ( $dom->find( '*:not(php)' )->contents() as $key => $el ) {

			/**
			 * Mark text nodes parents as editable elements unless, text node empty
			 */
			if ( $el->nodeType === XML_TEXT_NODE && preg_match( '/\S/', $el->nodeValue ) ) {
				pq( $el )->parent()->addClass( 'sle-editable-text' );
			}

		}

		ob_start();
		eval( '?>' . $dom->php() . '<?php;' );
		$this_string = ob_get_contents();
		ob_end_flush();

		return;

	}

}
