(function( $ ) {
	'use strict';

	$(function() {

		// An object holding the lists of edited contents
		var content = {
			texts: [],
			images: []
		};

		var	file_frame,
			target;

		/**
		 * Text editing
		 */
		$( 'body' ).on( 'click', '.sle-edit-link', function( event ) {

			event.preventDefault();
			event.stopPropagation();

			target = $( '.sle-editable-link[data-sle-dom-index=' + $( this ).data( 'sle-target' ) + ']' );

			$( '.sle-link-editor' ).val( $( target ).attr( 'href' ) );
			tb_show( 'Edit Link', '#TB_inline?width=600&height=550&inlineId=sle-link-modal' );
		});

		$( 'body' ).on( 'click', '.sle-editable-text', function( event ) {

			event.preventDefault();
			event.stopPropagation();

			if ( $( e.target ).is( 'a' ) ) {
				return false;
			}

			var element = this,
				settings = window.tinyMCEPreInit.mceInit['sle-editor'];

			settings.setup = function( editor ) {
				editor.on('change input blur keyup paste copy cut delete mouseup', function()  {

					// Update the element
					$( element ).html( editor.getContent() );

					// Add the changed text to our content object
					content.texts[ $( element ).data( 'sle-dom-index' ) ] = editor.getContent();

					// Tell the customize view, we have unsaved content
					parent.wp.customize.state( 'saved' ).set( false );
				});
			};

			settings.forced_root_block = false;

			tinyMCE.remove();
			tb_show( 'Edit Content', '#TB_inline?width=600&height=550&inlineId=sle-editor-modal' );
			tinyMCE.init( settings );
			tinyMCE.get( 'sle-editor' ).setContent( $( element ).html() );
		});

		/**
		 * Image editing
		 */

		 // Wrap the editable images to display the edit icon
		$( '.sle-editable-image' ).each( function( index ) {
			$( this )
			.wrap( '<div class="sle-image-wrapper"></div>' )
			.after( '<a href="javascript:;" class="sle-image-edit-icon sle-edit-image" data-sle-target="' + $( this ).data( 'sle-dom-index' ) + '"></a>' );
		});
/*
		$( '.sle-editable-link' ).each( function( index ) {
			$( this )
			.after( '<a href="javascript:;" class="sle-link-edit-icon sle-edit-link" data-sle-target="' + $( this ).data( 'sle-dom-index' ) + '"></a>' );
		});*/

		$( '[data-sle-dom-index]' ).each( function( index ) {

			if ( $( this ).css( 'background-image' ) !== 'none' ) {
				$( this )
				.after( '<a href="javascript:;" class="sle-image-edit-icon sle-edit-image" data-sle-target="' + $( this ).data( 'sle-dom-index' ) + '"></a>' );
			}
		});

		// Position the edit icons to the center of the editable images
/*		$( '.sle-image-edit-icon' ).each( function() {

			// Get the image and icon dimensions
			var $image = $( this ).siblings( '.sle-editable-image' ).first(),
				imageHeight = $image.height(),
				imageWidth = $image.width(),
				imageTopOffset = parseInt( $image.css( 'marginTop' ) ) + parseInt( $image.css( 'paddingTop' ) ) + parseInt( $image.css( 'borderTop' ) ),
				iconHeight = $( this ).outerHeight( true ),
				iconWidth = $( this ).outerWidth( true ),
				css = { top: imageTopOffset + imageHeight / 2 - iconHeight / 2 };

			if ( $image.css('float') === 'right' ) {
				var positionSide = 'Right';
			} else {
				var positionSide = 'Left';
			}

			// Calculate the position
			var imageSideOffset = parseInt( $image.css( 'margin' + positionSide ) ) + parseInt( $image.css( 'padding' + positionSide ) ) + parseInt( $image.css( 'border' + positionSide ) );
			css[ positionSide.toLowerCase() ] = imageSideOffset + imageWidth / 2 - iconWidth / 2;

			// Set the position
			$( this ).css( css );

		});*/

		// Launch the image selector when and image has been clicked
		$( 'body' ).on( 'click', '.sle-edit-image', function( event ) {

			event.preventDefault();
			event.stopPropagation();

			target = $( '.sle-editable-image[data-sle-dom-index=' + $( this ).data( 'sle-target' ) + ']' );

			// If the media frame already exists, reopen it.
			if ( file_frame ) {
				file_frame.open();
				return;
			}

			// Create the media frame.
			file_frame = parent.wp.media.frames.file_frame = parent.wp.media({
				multiple: false
			});

			// When an image is selected, run a callback.
			file_frame.on( 'select', function() {

				// We set multiple to false so only get one image from the uploader
				var attachment = file_frame.state().get( 'selection' ).first().toJSON();

				// Change the image src
				$( target ).attr( 'src', attachment.url );

				// Add to list of changes
				content.images[ $( target ).data( 'sle-dom-index' ) ] = attachment.url;

				// Trigger unsaved state
				parent.wp.customize.state( 'saved' ).set( false );

			});

			// Finally, open the modal
			file_frame.open();

		 });

		/**
		 * Save data
		 */
		
		// Bind to the customize view saved event
		parent.wp.customize.bind( 'saved', function() {

			// The data to save
			var data = {
				'action': 'sle_save_content',
				'template': sleSettings.page_template,
				'content': content,
			};

			// Post the data
			$.post( sleSettings.ajax_url, data, function( response ) {
				
			});

		});
	
	});

})( jQuery );
