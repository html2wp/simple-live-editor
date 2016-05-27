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
		$( 'body' ).on( 'click', '.sle-edit-text', function( event ) {

			var $target = $( '.sle-editable-text[data-sle-dom-index=' + $( this ).data( 'sle-target' ) + ']' ),
				settings = window.tinyMCEPreInit.mceInit['sle-editor'];

			settings.setup = function( editor ) {
				editor.on('change input blur keyup paste copy cut delete mouseup', function()  {

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
			tb_show( 'Edit Content', '#TB_inline?width=600&height=550&inlineId=sle-editor-modal' );
			tinyMCE.init( settings );
			tinyMCE.get( 'sle-editor' ).setContent( $target.html() );
		});

		/**
		 * Link editing
		 */
		$( 'body' ).on( 'click', '.sle-edit-link', function( event ) {

			target = $( '.sle-editable-link[data-sle-dom-index=' + $( this ).data( 'sle-target' ) + ']' );

			$( '.sle-link-editor' ).val( $( target ).attr( 'href' ) );
			tb_show( 'Edit Link', '#TB_inline?width=600&height=550&inlineId=sle-link-modal' );
		});

		/**
		 * Image editing
		 * Launch the image selector when and image has been clicked
		 */
		$( 'body' ).on( 'click', '.sle-edit-image', function( event ) {

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

		/**
		 * Position the edit icons
		 */
		$( '.sle-edit-icon' ).each( function() {

			// Get the target element and icon dimensions
			var $target = $( '[data-sle-dom-index=' + $( this ).data( 'sle-target' ) + ']' ),
				targetOffset = $target.offset(),
				css = { top: targetOffset.top };

			if ( $( this ).hasClass( 'sle-edit-icon--right' ) ) {
				var targetWidth = $target.width(),
					iconWidth = $( this ).outerWidth( true );

				css.left = targetOffset.left +  ( targetWidth - iconWidth );
			} else {
				css.left = targetOffset.left;
			}

			// Set the position
			$( this ).css( css );

		});

		/**
		 * Hover effects for the edit icons
		 */
		
		$( '[class^="sle-editable-"], [class*=" sle-editable-"]' ).on( 'mouseover', function( event ) {
			$( '.sle-edit-icon[data-sle-target=' + $( this ).data( 'sle-dom-index' ) + ']' ).show();
		});

		$( '[class^="sle-editable-"], [class*=" sle-editable-"]' ).on( 'mouseout', function( event ) {
			console.log( event.relatedTarget );
			if ( ! $( event.relatedTarget ).is( '.sle-edit-icon[data-sle-target=' + $( this ).data( 'sle-dom-index' ) + ']' ) ) {
				$( '.sle-edit-icon[data-sle-target=' + $( this ).data( 'sle-dom-index' ) + ']' ).hide();
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
