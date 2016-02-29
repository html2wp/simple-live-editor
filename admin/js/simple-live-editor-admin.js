(function( $ ) {
	'use strict';

	$(function() {

		var content = {
			texts: [],
			images: []
		};

		/**
		 * Text editing
		 */

		var editor = new MediumEditor( '.sle-editable-text:not(a)', {
		    toolbar: {
		        buttons: [ 'anchor' ]
		    }
		});

		$( 'body' ).on( 'input blur keyup paste copy cut delete mouseup', '.sle-editable-text', function( e ) {

			content.texts[ $( event.target ).data( 'sle-dom-index' ) ] = $( event.target ).html();

			parent.wp.customize.state( 'saved' ).set( false );

		});

		// See: http://stackoverflow.com/a/20398132/3073849
		$( '.sle-editable-text' ).keypress( function( event ) {

			if ( ! MediumEditor.util.isKey( event, MediumEditor.util.keyCode.ENTER ) ) {
				return true;
			}

			var node = event.target;

			if ( ! MediumEditor.util.isBlockContainer( node ) ) {
				return true;
			}

			var documentFragment = document.createDocumentFragment();

			// Add a new line
			var newElement = document.createTextNode( '\n' );
			documentFragment.appendChild( newElement );

			// Add the br
			newElement = document.createElement( 'br' );
			documentFragment.appendChild( newElement );

			var range = window.getSelection().getRangeAt( 0 );

			if ( range.endContainer.length === range.endOffset ) {
				newElement = document.createElement( 'br' );
				documentFragment.appendChild( newElement );
			}

			// Make the br replace selection
			range.deleteContents();
			range.insertNode( documentFragment );

			// Create a new range
			range = document.createRange();
			range.setStartAfter( newElement );
			range.collapse( true );

			// Set the cursor there
			var selection = window.getSelection();
			selection.removeAllRanges();
			selection.addRange( range );

			return false;

		});

		/**
		 * Image editing
		 */
		
		var file_frame,
			target;

		$( '.sle-editable-image' ).each( function( index ) {
			$( this )
			.wrap( '<div class="sle-image-wrapper"></div>' )
			.after( '<a href="javascript:;" class="sle-image-edit-icon sle-js-edit-image" data-sle-target="' + $( this ).data( 'sle-dom-index' ) + '"></a>' );
		});

		$( '.sle-image-edit-icon' ).each( function() {

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

			var imageSideOffset = parseInt( $image.css( 'margin' + positionSide ) ) + parseInt( $image.css( 'padding' + positionSide ) ) + parseInt( $image.css( 'border' + positionSide ) );
			css[ positionSide.toLowerCase() ] = imageSideOffset + imageWidth / 2 - iconWidth / 2;

			$( this ).css( css );

		});

		$( 'body' ).on( 'click', '.sle-js-edit-image, .sle-editable-image', function( event ) {

		    event.preventDefault();

		    if ( $( event.target ).is( '.sle-editable-image' ) ) {
		    	target = event.target;
		    } else {
		    	target = $( '.sle-editable-image[data-sle-dom-index=' + $( this ).data( 'sle-target' ) + ']' );
		    }

		    // If the media frame already exists, reopen it.
		    if ( file_frame ) {
		    	file_frame.open();
		    	return;
		    }

		    // Create the media frame.
		    file_frame = parent.wp.media.frames.file_frame = parent.wp.media({
		     	multiple: false  // Set to true to allow multiple files to be selected
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
		
		parent.wp.customize.bind( 'saved', function() {

			var data = {
				'action': 'sle_save_content',
				'template': sleSettings.page_template,
				'content': content,
			};

			$.post( sleSettings.ajax_url, data, function( response ) {
				
			});

		});
	
	});

})( jQuery );
