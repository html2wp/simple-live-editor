(function( $ ) {
	'use strict';

	$(function() {

		$( '.sle-editable-text' ).attr( 'contenteditable', 'true' );

		$( 'body' ).on( 'input blur keyup paste copy cut delete mouseup', '.sle-editable-text', function( e ) {

			parent.wp.customize.state( 'saved' ).set( false );

		});


		parent.wp.customize.bind( 'saved', function() {

			var data = {
				'action': 'sle_save_content',
				'template': sleSettings.page_template,
				'content': [],
			};

			$( '.sle-editable-text' ).each( function( index ) {
				data.content[ $( this ).data( 'sle-dom-index' ) ] = $( this ).html();
			});

			$.post( sleSettings.ajax_url, data, function( response ) {
				
			});

		});
	
	});

})( jQuery );
