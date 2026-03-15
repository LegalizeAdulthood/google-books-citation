<?php
// SPDX-License-Identifier: GPL-2.0-only

class GoogleBooksCitationHooks {

	/**
	 * Register the <googlebooks> parser tag.
	 *
	 * @param Parser $parser
	 */
	public static function onParserFirstCallInit( Parser $parser ) {
		$parser->setHook( 'googlebooks', [ self::class, 'renderTag' ] );
	}

	/**
	 * Load the toolbar module on edit pages.
	 *
	 * @param OutputPage $out
	 * @param Skin $skin
	 */
	public static function onBeforePageDisplay( OutputPage $out, Skin $skin ) {
		$action = Action::getActionName( $out );
		if ( $action === 'edit' || $action === 'submit' ) {
			$out->addModules( 'ext.googleBooksCitation.toolbar' );
		}
	}

	/**
	 * Render the <googlebooks> tag.
	 *
	 * Usage: <googlebooks>https://books.google.com/books?id=...&pg=...</googlebooks>
	 *
	 * @param string $input The URL inside the tag
	 * @param array $args Tag attributes (unused)
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @return string Wikitext citation output
	 */
	public static function renderTag( $input, array $args, Parser $parser, PPFrame $frame ) {
		$url = trim( $input );
		if ( $url === '' ) {
			return '<span class="error">' .
				htmlspecialchars( wfMessage( 'googlebooks-citation-error-no-url' )->text() ) .
				'</span>';
		}

		$citationParser = new GoogleBooksCitationParser();
		$result = $citationParser->buildCitation( $url );

		if ( $result === false ) {
			return '<span class="error">' .
				htmlspecialchars( wfMessage( 'googlebooks-citation-error-fetch' )->text() ) .
				'</span>';
		}

		return [ $result, 'noparse' => false ];
	}
}
