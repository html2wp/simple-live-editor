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
	 * HTML purifier config
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      HTMLPurifier_Config    $purifier_config    HTML purifier config
	 */
	private $purifier_config;

	/**
	 * HTML purifier
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      HTMLPurifier    $purifier    HTML purifier
	 */
	private $purifier;

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
		$this->purifier_config = HTMLPurifier_Config::createDefault();
		$this->purifier = new HTMLPurifier( $this->purifier_config );

	}

	/**
	 * Register the stylesheets for the customize view
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * The function gets loaded in the public scope,
		 * so we'll need to check that we are acutally in the customize view
		 */
		global $wp_customize;

		if ( isset( $wp_customize ) ) {

			// Medium-editor
			wp_enqueue_style( 'medium-editor', Helpers::get_dir_url( __FILE__ ) . '../node_modules/medium-editor/dist/css/medium-editor.css' );
			wp_enqueue_style( 'medium-editor-theme', Helpers::get_dir_url( __FILE__ ) . '../node_modules/medium-editor/dist/css/themes/bootstrap.css', array( 'medium-editor' ) );

			// The plugin stylesheet
			wp_enqueue_style( $this->plugin_name, Helpers::get_dir_url( __FILE__ ) . 'css/simple-live-editor-admin.css', array( 'dashicons',  'medium-editor-theme' ), $this->version, 'all' );
		}

	}

	/**
	 * Register the JavaScript for the customize view
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * The function gets loaded in the public scope,
		 * so we'll need to check that we are acutally in the customize view
		 */
		global $wp_customize;

		if ( isset( $wp_customize ) ) {

			// Medium-editor
			wp_enqueue_script( 'medium-editor', Helpers::get_dir_url( __FILE__ ) . '../node_modules/medium-editor/dist/js/medium-editor.js' );

			// The plugin javascript
			wp_enqueue_script( $this->plugin_name, Helpers::get_dir_url( __FILE__ ) . 'js/simple-live-editor-admin.js', array( 'jquery', 'medium-editor' ), $this->version, false );
			wp_localize_script( $this->plugin_name, 'sleSettings', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'page_template' => get_page_template() ) );
		}

	}

	/**
	 * Adds the necessary styles for the page editing view notice
	 *
	 * @since    1.0.1
	 */
	public function enqueue_sle_notice_styles() {
		wp_enqueue_style( $this->plugin_name . '-notify', Helpers::get_dir_url( __FILE__ ) . 'css/simple-live-editor-notify.css' );
	}

	/**
	 * Prints notice in page editing view to let the user know about the availability of the plugin and editin capability
	 *
	 * @since    1.0.1
	 */
	public function show_sle_notice() {
		$message = esc_html__( 'Want to edit text and images? Use Live Editing in the Customize view.', $this->plugin_name );
		$cta = esc_html__( 'Launch Customizer', $this->plugin_name );
		$cta_url = admin_url( 'customize.php' );

		if ( isset( $_GET['post'] ) ) {
			$post = get_post( $_GET['post'] );
			if (!empty($post) && isset($post->ID)) {
				$cta_url = admin_url( 'customize.php?url=' . rawurlencode( get_permalink($post->ID) ) );
			}
		}
		
		echo '<div class="notice notice-info sle-notice"><p><span class="dashicons dashicons-edit sle-notice-edit"></span>' . $message . '<a href="' . $cta_url . '" class="btn">' . $cta . '&rarr;</a></p></div>';
	}

	/**
	 * Override the template output in the customize view
	 *
	 * @since    1.0.0
	 */
	public function prepare_template_for_editing( $template ) {

		/**
		 * The function gets loaded in the public scope,
		 * so we'll need to check that we are acutally in the customize view
		 */
		global $wp_customize;

		if ( !isset( $wp_customize ) ) {
			return $template;
		}

		// Get the document
		$this->get_document( $template );

		/**
		 * Output the document
		 */
		ob_start();
		eval( '?>' . $this->dom->php() );
		$this_string = ob_get_contents();
		ob_end_flush();

		// No need to return a template name anymore
		return;

	}

	/**
	 * Save the content that has been edited
	 *
	 * @since    1.0.0
	 */
	public function save_content() {

		// Check that we have the necessary fields
		if ( !isset( $_POST['template'] ) || !isset( $_POST['content'] ) ) {
			wp_die();
		}

		// Get the document
		$this->get_document( $_POST['template'] );

		/**
		 * Save the text content
		 */
		if ( isset( $_POST['content']['texts'] ) ) {

			// Loop through the JSON of edited texts and change the value to document
			foreach ( array_filter( $_POST['content']['texts'] ) as $index => $html ) {
				$this->dom->find( ".sle-editable-text[data-sle-dom-index=$index]" )->html( $this->purifier->purify( stripslashes( $html ) ) );
			}

		}

		/**
		 * Save the images
		 */
		if ( isset( $_POST['content']['images'] ) ) {

			// Loop through the JSON of edited images and change the values to document
			foreach ( array_filter( $_POST['content']['images'] ) as $index => $src ) {

				$src = esc_url( stripslashes( $src ) );
				$element = $this->dom->find( ".sle-editable-image[data-sle-dom-index=$index]" )->get(0);

				// If we have a data-src attribute use that
				if ( pq( $element )->is( '[data-src]' ) ) {
					pq( $element )->attr( 'data-src', $src );
				} else {
					pq( $element )->attr( 'src', $src );
				}

			}

		}

		// Save document
		$this->save_document( $_POST['template'] );

		wp_die();

	}

	/**
	 * Load the document to DOM and mark the elements
	 *
	 * @since    1.0.0
	 */
	private function get_document( $template ) {

		$this->dom = phpQuery::newDocumentFilePHP( $template );

		/**
		 * Find all text nodes and mark their parents as editable elements
		 */
		foreach ( $this->dom->find( '*:not(php)' )->contents() as $key => $element ) {

			// Don't do anything if parent already an editable field
			if ( count( pq( $element )->parents( '.sle-editable-text' ) ) > 0 ) {
				continue;
			}

			// Don't do anything if any of the siblings are php tags
			if ( count( pq( $element )->siblings( 'php' ) ) > 0 ) {
				continue;
			}

			// Mark text nodes parents as editable elements unless, text node empty
			if ( $element->nodeType === XML_TEXT_NODE && preg_match( '/\S/', $element->nodeValue ) ) {

				// If the parent is not 'Phrasing content or headings or paragraph' and the acutal element has siblings, wrap the element with div
				if ( count( pq( $element )->parent()->not( SLE_PHRASING_CONTENT )->not( SLE_HEADING_CONTENT )->not( 'p' ) ) > 0 && count( pq( $element )->siblings() ) > 0 ) {
					pq( $element )->wrap( '<div class="sle-wrapper-element"></div>' );
				}

				// Mark as editable
				pq( $element )->parent()->addClass( 'sle-editable-text' );
			}

		}

		/**
		 * Wrap p tags for better editing functionality
		 */
		foreach ( $this->dom->find( 'p.sle-editable-text' ) as $key => $element ) {

			// Don't do anything if parent already an editable field
			if ( count( pq( $element )->parents( '.sle-editable-text' ) ) > 0 ) {
				continue;
			}

			// Do the wrapping
			pq( $element )->nextAll( 'p.sle-editable-text' )->andSelf()->removeClass( 'sle-editable-text' )->wrapAll( '<div class="sle-wrapper-element sle-editable-text"></div>' );

		}

		/**
		 * Find all images mark them as editable elements
		 */
		$this->dom->find( 'img' )->addClass( 'sle-editable-image' );


		/**
		 * Create our indexing for all HTML elements
		 */
		foreach ( $this->dom->find( '*:not(php)' ) as $key => $element ) {
			pq( $element )->attr( 'data-sle-dom-index', $key );
		}

	}

	/**
	 * Save the document
	 *
	 * @since    1.0.0
	 */
	private function save_document( $template ) {

		/**
		 * Get rid of wrappers
		 */
		foreach ( $this->dom->find( '.sle-wrapper-element' ) as $key => $element ) {
			pq( $element )->replaceWith( pq( $element )->php() );
		}

		/**
		 * Remove tmp attributes and classes
		 */
		$this->dom->find( '*:not(php)' )->removeAttr( 'data-sle-dom-index' )->removeClass( 'sle-editable-text sle-editable-image' );

		/**
		 * Save the document to template
		 */
		file_put_contents( $template, $this->dom->php() );

	}

}
