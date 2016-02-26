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
			wp_enqueue_style( 'medium-editor', Helpers::get_dir_url( __FILE__ ) . '../node_modules/medium-editor/dist/css/medium-editor.css' );
			wp_enqueue_style( 'medium-editor-theme', Helpers::get_dir_url( __FILE__ ) . '../node_modules/medium-editor/dist/css/themes/bootstrap.css', array( 'medium-editor' ) );
			wp_enqueue_style( $this->plugin_name, Helpers::get_dir_url( __FILE__ ) . 'css/simple-live-editor-admin.css', array( 'dashicons',  'medium-editor-theme' ), $this->version, 'all' );
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
			wp_enqueue_script( 'medium-editor', Helpers::get_dir_url( __FILE__ ) . '../node_modules/medium-editor/dist/js/medium-editor.js' );
			wp_enqueue_script( $this->plugin_name, Helpers::get_dir_url( __FILE__ ) . 'js/simple-live-editor-admin.js', array( 'jquery', 'medium-editor' ), $this->version, false );
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
		eval( '?>' . $this->dom->php() );
		$this_string = ob_get_contents();
		ob_end_flush();

		return;

	}


	public function save_content() {

		$purifier_config = HTMLPurifier_Config::createDefault();
		$purifier = new HTMLPurifier( $purifier_config );

		if ( !isset( $_POST['template'] ) || !isset( $_POST['content'] ) ) {
			wp_die();
		}

		$this->get_document( $_POST['template'] );

		if ( isset( $_POST['content']['texts'] ) ) {

			foreach ( array_filter( $_POST['content']['texts'] ) as $index => $html ) {
				$this->dom->find( ".sle-editable-text[data-sle-dom-index=$index]" )->html( $purifier->purify( stripslashes( $html ) ) );
			}

		}

		if ( isset( $_POST['content']['images'] ) ) {

			foreach ( array_filter( $_POST['content']['images'] ) as $index => $src ) {

				$src = esc_url( stripslashes( $src ) );
				$element = $this->dom->find( ".sle-editable-image[data-sle-dom-index=$index]" )->first();

				if ( $element->is( '[data-src]' ) ) {
					$element->attr( 'data-src', $src );
				} else {
					$element->attr( 'src', $src );
				}

			}

		}

		$this->save_document( $_POST['template'] );

		wp_die();

	}


	private function get_document( $template ) {

		$phrasing_content = 'a, abbr, map area, audio, b, bdi, bdo, br, button, canvas, cite, code, data, datalist, del, dfn, em, embed, i, iframe, img, input, ins, kbd, keygen, label, map, mark, math, meter, noscript, object, output, progress, q, ruby, s, samp, script, select, small, span, strong, sub, sup, svg, template, textarea, time, u, var, video, wbr, text';

		$heading_content = 'h1, h2, h3, h4, h5, h6';

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
				if ( count( pq( $element )->parent()->not( $phrasing_content )->not( $heading_content )->not( 'p' ) ) > 0 && count( pq( $element )->siblings() ) > 0 ) {
					pq( $element )->wrap( '<div class="sle-wrapper-element"></div>' );
				}

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


	private function save_document( $template ) {

		// Get rid of wrappers
		foreach ( $this->dom->find( '.sle-wrapper-element' ) as $key => $element ) {

			pq( $element )->replaceWith( pq( $element )->php() );

		}

		// Remove tmp attributes and classes
		$this->dom->find( '*:not(php)' )->removeAttr( 'data-sle-dom-index' )->removeClass( 'sle-editable-text sle-editable-image' );

		file_put_contents( $template, $this->dom->php() );

	}

}
