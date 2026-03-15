<?php

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
