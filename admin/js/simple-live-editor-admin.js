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

		// Load medium-editor
		var editor = new MediumEditor( '.sle-editable-text:not(a)', {
		    toolbar: {
		        buttons: [ 'anchor' ]
		    }
		});

		// On text change, prepare for saving
		$( 'body' ).on( 'input blur keyup paste copy cut delete mouseup', '.sle-editable-text', function( e ) {

			// Add the changed text to our content object
			content.texts[ $( event.target ).data( 'sle-dom-index' ) ] = $( event.target ).html();

			// Tell the customize view, we have unsaved content
			parent.wp.customize.state( 'saved' ).set( false );

		});

		// Chrome inputs <div>s as line breaks, change these to <br>s
		// See: http://stackoverflow.com/a/20398132/3073849
		$( '.sle-editable-text' ).keypress( function( event ) {

			// Make sure ENTER was pressed
			if ( ! MediumEditor.util.isKey( event, MediumEditor.util.keyCode.ENTER ) ) {
				return true;
			}

			var node = event.target;

			// Make sure the element is not a block container
			// In block containers we want to use the wrapper elements
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

			// Check if selection is on end of the line, as we will then need double <br>
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

		 // Wrap the editable images to display the edit icon
		$( '.sle-editable-image' ).each( function( index ) {
			$( this )
			.wrap( '<div class="sle-image-wrapper"></div>' )
			.after( '<a href="javascript:;" class="sle-image-edit-icon sle-js-edit-image" data-sle-target="' + $( this ).data( 'sle-dom-index' ) + '"></a>' );
		});

		// Position the edit icons to the center of the editable images
		$( '.sle-image-edit-icon' ).each( function() {

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

		});

		// Launch the image selector when and image has been clicked
		$( 'body' ).on( 'click', '.sle-js-edit-image, .sle-editable-image', function( event ) {

		    event.preventDefault();

		    // Choose the target based on where the user clicked
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
