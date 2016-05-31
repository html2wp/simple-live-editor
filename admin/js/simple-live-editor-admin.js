(function( $ ) {
	'use strict';

	$(function() {

		// An object holding the lists of edited contents
		var content = {
			texts: [],
			images: [],
			bgImages: [],
			links: []
		};

		/**
		 * Text editing
		 */
		$( 'body' ).on( 'click', '.sle-edit-text', function( event ) {

			var $target = $( '.sle-editable-text[data-sle-dom-index=' + $( this ).data( 'sle-target' ) + ']' ),
				settings = window.tinyMCEPreInit.mceInit['sle-editor'];

			settings.setup = function( editor ) {
				editor.on( 'change input blur keyup paste copy cut delete mouseup', function() {

					// Update the element
					$target.html( editor.getContent() );

					// Add the changed text to our content object
					content.texts[ $target.data( 'sle-dom-index' ) ] = editor.getContent();

					// Tell the customize view, we have unsaved content
					parent.wp.customize.state( 'saved' ).set( false );
				});
			};

			settings.forced_root_block = false;

			tinyMCE.remove();
			tb_show( 'Edit Content', '#TB_inline?width=700&height=450&inlineId=sle-editor-modal' );
			tinyMCE.init( settings );
			tinyMCE.get( 'sle-editor' ).setContent( $target.html() );
		});

		/**
		 * Link editing
		 */
		$( 'body' ).on( 'click', '.sle-edit-link', function( event ) {

			var $target = $( '.sle-editable-link[data-sle-dom-index=' + $( this ).data( 'sle-target' ) + ']' );

			$( '.sle-link-editor' ).val( $target.attr( 'href' ) );
			tb_show( 'Edit Link', '#TB_inline?width=700&height=450&inlineId=sle-link-modal' );

			$( '.sle-link-editor' ).off( 'change input blur keyup paste copy cut delete mouseup' );

			$( '.sle-link-editor' ).on( 'change input blur keyup paste copy cut delete mouseup', function() {

				// Update the change to dom
				$target.attr( 'href', $( this ).val() );

				// Add the changed link to our content object
				content.links[ $target.data( 'sle-dom-index' ) ] = $( this ).val();

				// Tell the customize view, we have unsaved content
				parent.wp.customize.state( 'saved' ).set( false );
			});
		});

		/**
		 * Image editing
		 */
		$( 'body' ).on( 'click', '.sle-edit-image', function( event ) {

			var $target = $( '.sle-editable-image[data-sle-dom-index=' + $( this ).data( 'sle-target' ) + ']' );

			open_file_frame( function( url ) {

				// Change the image src
				$target.attr( 'src', url );

				// Add to list of changes
				content.images[ $target.data( 'sle-dom-index' ) ] = url;

				// Trigger unsaved state
				parent.wp.customize.state( 'saved' ).set( false );

			});

		 });

		/**
		 * Background image editing
		 */
		$( 'body' ).on( 'click', '.sle-edit-bg-image', function( event ) {

			var $target = $( '.sle-editable-bg-image[data-sle-dom-index=' + $( this ).data( 'sle-target' ) + ']' );

			open_file_frame( function( url ) {

				// Replace the first url found with the new url
				var backgroundImage = $target.css( 'background-image' ).replace( /url\((.*?)\)/i, 'url(' + url + ')' );

				// Change the image src
				$target.css( 'background-image', backgroundImage );

				// Add to list of changes
				content.bgImages[ $target.data( 'sle-dom-index' ) ] = $target.attr( 'style' );

				// Trigger unsaved state
				parent.wp.customize.state( 'saved' ).set( false );

			});

		 });

		/**
		 * Launch the image selector when and image has been clicked
		 */
		function open_file_frame( callback ) {

			// Create the media frame.
			var	file_frame = parent.wp.media.frames.file_frame = parent.wp.media({
				multiple: false
			});

			// When an image is selected, run a callback.
			file_frame.on( 'select', function() {

				// We set multiple to false so only get one image from the uploader
				var attachment = file_frame.state().get( 'selection' ).first().toJSON();

				callback( attachment.url );

			});

			// Finally, open the modal
			file_frame.open();
		}

		/**
		 * The editing icons
		 */

		 // Wrap the editable images to display the edit icon
		$( '.sle-editable-image' ).each( function( index ) {
			$( 'body' ).append( '<a href="javascript:;" class="sle-edit-icon sle-edit-icon--pen sle-edit-image" data-sle-target="' + $( this ).data( 'sle-dom-index' ) + '"></a>' );
		});

		$( '.sle-editable-text' ).each( function( index ) {
			$( 'body' ).append( '<a href="javascript:;" class="sle-edit-icon sle-edit-icon--pen sle-edit-text" data-sle-target="' + $( this ).data( 'sle-dom-index' ) + '"></a>' );
		});

		$( '.sle-editable-link' ).each( function( index ) {
			$( 'body' ).append( '<a href="javascript:;" class="sle-edit-icon sle-edit-icon--link sle-edit-icon--right sle-edit-link" data-sle-target="' + $( this ).data( 'sle-dom-index' ) + '"></a>' );
		});

		$( '[data-sle-dom-index]' ).each( function( index ) {

			if ( $( this ).css( 'background-image' ) !== 'none' ) {
				$( this ).addClass( 'sle-editable-bg-image' );
				$( 'body' ).append( '<a href="javascript:;" class="sle-edit-icon sle-edit-icon--pen sle-edit-bg-image" data-sle-target="' + $( this ).data( 'sle-dom-index' ) + '"></a>' );
			}
		});

		function positionIcon( icon ) {

			// Get the target element and icon dimensions
			var $target = $( '[data-sle-dom-index=' + $( icon ).data( 'sle-target' ) + ']' ),
				targetOffset = $target.offset(),
				css = { top: targetOffset.top };

			if ( $( icon ).hasClass( 'sle-edit-icon--right' ) ) {
				var targetWidth = $target.width(),
					iconWidth = $( icon ).outerWidth( true );

				css.left = targetOffset.left +  ( targetWidth - iconWidth );
			} else {
				css.left = targetOffset.left;
			}

			// Set the position
			$( icon ).css( css );
		}

		/**
		 * Position the edit icons
		 */
		$( '.sle-edit-icon' ).each( function() {

			var icon = this;

			positionIcon( icon );

			setInterval( function() { positionIcon( icon ) }, 200 );

		});

		/**
		 * Hover effects for the edit icons
		 */
		
		$( '[class^="sle-editable-"], [class*=" sle-editable-"]' ).on( 'mouseover', function( event ) {
			$( '.sle-edit-icon[data-sle-target=' + $( this ).data( 'sle-dom-index' ) + ']' ).show();
		});

		$( '[class^="sle-editable-"], [class*=" sle-editable-"]' ).on( 'mouseout', function( event ) {
			var $target = $( '.sle-edit-icon[data-sle-target=' + $( this ).data( 'sle-dom-index' ) + ']' );

			// TODO: if the element is a sibling and contained by the element don't end the hover
			if ( ! $( event.relatedTarget ).is( '.sle-edit-icon[data-sle-target=' + $( this ).data( 'sle-dom-index' ) + ']' ) ) {
				$target.hide();
			}
		});

		$( '.sle-edit-icon' ).on( 'mouseover', function( event ) {
			$( '[data-sle-dom-index=' + $( this ).data( 'sle-target' ) + ']' ).addClass( 'sle-hover' );
		});

		$( '.sle-edit-icon' ).on( 'mouseout', function( event ) {

			$( '[data-sle-dom-index=' + $( this ).data( 'sle-target' ) + ']' ).removeClass( 'sle-hover' );

			if ( ! $( event.relatedTarget ).is( '[data-sle-dom-index=' + $( this ).data( 'sle-target' ) + ']' ) ) {
				$( this ).hide();
			}
		});

		function isContainedByElement( element, container ) {

			var elementOffset = $( element ).offset(),
				elementWidth = $( element ).width(),
				elementHeight = $( element ).height(),
				containerOffset = $( container ).offset(),
				containerWidth = $( container ).outerWidth( true ),
				containerHeight = $( container ).outerHeight( true );

			if ( ! elementOffset || ! containerOffset ) {
				return false;
			}

			if ( elementOffset.left >= containerOffset.left
					&& elementOffset.top >= containerOffset.top
					&& elementOffset.left + elementWidth <=  containerOffset.left + containerWidth
					&& elementOffset.top + elementHeight <=  containerOffset.top + containerHeight ) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Save data
		 */
		
		// Bind to the customize view saved event
		parent.wp.customize.bind( 'saved', function() {

			// The data to save
			var data = {
				'action': 'sle_save_content',
				'page_template': sleSettings.page_template,
				'page_id': sleSettings.page_id,
				'content': content,
			};

			if ( sleSettings.language_code ) {
				data.language_code = sleSettings.language_code;
			}

			// Post the data
			$.post( sleSettings.ajax_url, data, function( response ) {
				// TODO: reset content array
			});

		});
	
	});

})( jQuery );
