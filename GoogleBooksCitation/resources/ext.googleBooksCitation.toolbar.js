( function ( mw, $ ) {
	'use strict';

	function insertCitation() {
		var url = prompt( mw.msg( 'googlebooks-citation-toolbar-prompt' ) );
		if ( !url ) {
			return;
		}

		var api = new mw.Api();
		api.get( {
			action: 'googlebooks-citation',
			url: url
		} ).done( function ( data ) {
			if ( data && data.citation ) {
				$( '#wpTextbox1' ).textSelection( 'encapsulateSelection', {
					peri: data.citation,
					replace: true
				} );
			} else {
				mw.notify( mw.msg( 'googlebooks-citation-error-fetch' ), { type: 'error' } );
			}
		} ).fail( function () {
			mw.notify( mw.msg( 'googlebooks-citation-error-fetch' ), { type: 'error' } );
		} );
	}

	// Add toolbar button when WikiEditor is available
	mw.hook( 'wikiEditor.toolbarReady' ).add( function ( $textarea ) {
		$textarea.wikiEditor( 'addToToolbar', {
			section: 'main',
			group: 'insert',
			tools: {
				'googlebooks-citation': {
					label: mw.msg( 'googlebooks-citation-toolbar-button' ),
					type: 'button',
					oouiIcon: 'reference',
					action: {
						type: 'callback',
						execute: insertCitation
					}
				}
			}
		} );
	} );
}( mediaWiki, jQuery ) );
