<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://htmltowordpress.io/
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
	 * The DOM of current template
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $dom    The DOM of current template.
	 */
	private $dom;

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

		global $wp_customize;

		if ( isset( $wp_customize ) ) {
			wp_enqueue_style( $this->plugin_name, Helpers::get_dir_url( __FILE__ ) . 'css/simple-live-editor-admin.css', array( 'dashicons' ), $this->version, 'all' );
		}

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		global $wp_customize;

		if ( isset( $wp_customize ) ) {
			wp_enqueue_script( $this->plugin_name, Helpers::get_dir_url( __FILE__ ) . 'js/simple-live-editor-admin.js', array( 'jquery' ), $this->version, false );
			wp_localize_script( $this->plugin_name, 'sleSettings', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'page_template' => get_page_template() ) );
		}

	}


	public function prepare_template_for_editing( $template ) {

		global $wp_customize;

		if ( !isset( $wp_customize ) ) {
			return $template;
		}

		$this->get_document( $template );

		ob_start();
		eval( '?>' . $this->dom->php() . '<?php;' );
		$this_string = ob_get_contents();
		ob_end_flush();

		return;

	}


	public function save_content() {

		if ( !isset( $_POST['template'] ) || !isset( $_POST['content'] ) ) {
			wp_die();
		}

		$this->get_document( $_POST['template'] );

		if ( isset( $_POST['content']['texts'] ) ) {

			foreach ( array_filter( $_POST['content']['texts'] ) as $index => $html ) {
				$this->dom->find( ".sle-editable-text[data-sle-dom-index=$index]" )->html( stripslashes( $html ) );
			}

		}

		if ( isset( $_POST['content']['images'] ) ) {

			foreach ( array_filter( $_POST['content']['images'] ) as $index => $src ) {

				foreach ( $this->dom->find( ".sle-editable-image[data-sle-dom-index=$index]" ) as $key => $el ) {

					if ( pq( $el )->is( '[data-src]' ) ) {
						pq( $el )->attr( 'data-src', stripslashes( $src ) );
					} else {
						pq( $el )->attr( 'src', stripslashes( $src ) );
					}

				}

			}

		}

		$this->save_document( $_POST['template'] );

		wp_die();

	}


	private function get_document( $template ) {

		$this->dom = phpQuery::newDocumentFilePHP( $template );

		/**
		 * Create our indexing for all HTML elements
		 */
		foreach ( $this->dom->find( '*:not(php)' ) as $key => $el ) {

			pq( $el )->attr( 'data-sle-dom-index', $key );

		}

		/**
		 * Find all text nodes and mark their parents as editable elements
		 */
		foreach ( $this->dom->find( '*:not(php)' )->contents() as $key => $el ) {

			/**
			 * Mark text nodes parents as editable elements unless, text node empty
			 */
			if ( $el->nodeType === XML_TEXT_NODE && preg_match( '/\S/', $el->nodeValue ) ) {
				pq( $el )->parent()->addClass( 'sle-editable-text' );
			}

		}

		/**
		 * Find all images mark them as editable elements
		 */
		$this->dom->find( 'img' )->addClass( 'sle-editable-image' );

	}


	private function save_document( $template ) {

		$this->dom->find( '*:not(php)' )->removeAttr( 'data-sle-dom-index' )->removeClass( 'sle-editable-text' );

		file_put_contents( $template, $this->dom->php() );

	}

}
