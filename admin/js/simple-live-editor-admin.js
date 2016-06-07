(function( $ ) {
	'use strict';

	$(function() {

		// An object holding the lists of edited contents
		var content = {
			texts: {},
			images: {},
			bgImages: {},
			links: {},
			sections: {},
			newSections: {},
			removals: []
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
			settings.height = '300';

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

			openFileFrame( function( url ) {

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

			openFileFrame( function( url ) {

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
		function openFileFrame( callback ) {

			// Create the media frame.
			var	fileFrame = parent.wp.media.frames.file_frame = parent.wp.media({
				multiple: false
			});

			// When an image is selected, run a callback.
			fileFrame.on( 'select', function() {

				// We set multiple to false so only get one image from the uploader
				var attachment = fileFrame.state().get( 'selection' ).first().toJSON();

				callback( attachment.url );

			});

			// Finally, open the modal
			fileFrame.open();
		}

		/**
		 * The editing icons
		 */

		function positionIcon( icon ) {

			// Get the target element and icon dimensions
			var $target = $( '[data-sle-dom-index=' + $( icon ).data( 'sle-target' ) + ']' ),
				targetOffset = $target.offset();

			// If the element is hidden
			if ( targetOffset == null ) {
				return false;
			}

			var css = { top: targetOffset.top };

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

		function addEditIcons( element ) {

			// Wrap the editable images to display the edit icon
			$( element ).find( '.sle-editable-image' ).each( function( index ) {
				$( 'body' ).append( '<a href="javascript:;" class="sle-edit-icon sle-edit-icon--pen sle-edit-image" data-sle-target="' + $( this ).data( 'sle-dom-index' ) + '"></a>' );
			});

			$( element ).find( '.sle-editable-text' ).each( function( index ) {
				$( 'body' ).append( '<a href="javascript:;" class="sle-edit-icon sle-edit-icon--pen sle-edit-text" data-sle-target="' + $( this ).data( 'sle-dom-index' ) + '"></a>' );
			});

			$( element ).find( '.sle-editable-link' ).each( function( index ) {
				$( 'body' ).append( '<a href="javascript:;" class="sle-edit-icon sle-edit-icon--link sle-edit-icon--right sle-edit-link" data-sle-target="' + $( this ).data( 'sle-dom-index' ) + '"></a>' );
			});

			$( element ).find( '[data-sle-dom-index]' ).each( function( index ) {

				if ( $( this ).css( 'background-image' ) !== 'none' ) {
					$( 'body' ).append( '<a href="javascript:;" class="sle-edit-icon sle-edit-icon--pen sle-edit-bg-image" data-sle-target="' + $( this ).data( 'sle-dom-index' ) + '"></a>' );
					$( this ).addClass( 'sle-editable-bg-image' );
				}
			});

			/**
			 * Position the edit icons
			 */
			$( '.sle-edit-icon:not([data-sle-icon-inited])' ).each( function() {

				var icon = this;

				positionIcon( icon );

				setInterval( function() { positionIcon( icon ) }, 200 );

				$( this ).attr( 'data-sle-icon-inited', 'true' );

			});

		}

		addEditIcons( document.body );

		/**
		 * Hover effects for the edit icons
		 */
		
		$( 'body' ).on( 'mouseover', '[class^="sle-editable-"], [class*=" sle-editable-"]', function( event ) {
			$( '.sle-edit-icon[data-sle-target=' + $( this ).data( 'sle-dom-index' ) + ']' ).show();
		});

		$( 'body' ).on( 'mouseout', '[class^="sle-editable-"], [class*=" sle-editable-"]', function( event ) {
			var $target = $( '.sle-edit-icon[data-sle-target=' + $( this ).data( 'sle-dom-index' ) + ']' );

			// TODO: if the element is a sibling and contained by the element don't end the hover
			if ( ! $( event.relatedTarget ).is( '.sle-edit-icon[data-sle-target=' + $( this ).data( 'sle-dom-index' ) + ']' ) ) {
				$target.hide();
			}
		});

		$( 'body' ).on( 'mouseover', '.sle-edit-icon', function( event ) {
			$( '[data-sle-dom-index=' + $( this ).data( 'sle-target' ) + ']' ).addClass( 'sle-hover' );
		});

		$( 'body' ).on( 'mouseout', '.sle-edit-icon', function( event ) {

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
		 * Sections
		 */
		
		function saveSectionOrder( event ) {

			var indexes = [];

			$( event.to ).children( '[data-sle-dom-index]' ).each( function() {

				if ( $( this ).data( 'sle-dom-index-prefix' ) ) {
					indexes.push( $( this ).data( 'sle-dom-index-prefix' ) );
				} else {
					indexes.push( $( this ).data( 'sle-dom-index' ) );
				}
			});

			content.sections[ $( event.to ).data( 'sle-dom-index' ) ] = indexes;

			// Tell the customize view, we have unsaved content
			parent.wp.customize.state( 'saved' ).set( false );
		}

		// The list of sections in the ui
		$( '.wp-sections' ).each( function() {

			Sortable.create( this, {
				group: { name: 'sections', pull: true, put: true },
				ghostClass: 'sle-sortable-ghost',
				onAdd: function( event ) {

					// The data to save
					var data = {
						'action': 'sle_get_content',
						'template': $( event.item ).data( 'sle-section-template' )
					};

					$( event.item ).hide();

					// Get the data
					$.get( sleSettings.ajax_url, data, function( response ) {

						// Create section
						var $section = $( response );
						$section.insertBefore( event.item );

						// Save new section info
						content.newSections[ $section.data( 'sle-dom-index-prefix' ) ] = $( event.item ).data( 'sle-section-template' );

						// Remove the tmp item
						event.item.remove();

						// Add edit icons
						addEditIcons( $section );

						// Save new section order
						saveSectionOrder( event );
					});
				},
				onRemove: function( event ) {

					// Save the info about the removed element
					content.removals.push( $( event.item ).data( 'sle-dom-index' ) );

					// Tell the customize view, we have unsaved content
					parent.wp.customize.state( 'saved' ).set( false );

				},
				onUpdate: saveSectionOrder
			});
		});

		// The customizer list of sections
		Sortable.create( $( '.sle-sections-list', parent.document.body ).get( 0 ), {
			group: { name: 'sections', pull: 'clone', put: true },
			ghostClass: 'sle-sortable-ghost',
			sort: false,
			onAdd: function ( event ) {
				event.item.remove();
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