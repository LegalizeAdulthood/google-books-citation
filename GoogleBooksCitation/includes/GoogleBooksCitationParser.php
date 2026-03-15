<?php
// SPDX-License-Identifier: GPL-2.0-only

class GoogleBooksCitationParser {

	/**
	 * Build a wikitext citation string from a Google Books URL.
	 *
	 * @param string $url A Google Books URL
	 * @return string|false The citation wikitext, or false on failure
	 */
	public function buildCitation( $url ) {
		$html = $this->fetchHtml( $url );
		if ( $html === false ) {
			return false;
		}

		$result = $this->parseHtml( $html );

		return $this->formatCitation( $url, $result['publication'], $result['date'] );
	}

	/**
	 * Fetch HTML content from the given URL.
	 *
	 * @param string $url
	 * @return string|false
	 */
	protected function fetchHtml( $url ) {
		return Http::get( $url, [
			'timeout' => 30,
		] );
	}

	/**
	 * Parse HTML to extract publication title and date.
	 *
	 * Mirrors the Node.js logic:
	 *   publication = h1.gb-volume-title (text, with inner <span> removed)
	 *   date        = text of the <span> inside h1.gb-volume-title
	 *
	 * @param string $html Raw HTML
	 * @return array Associative array with 'publication' and 'date' keys
	 */
	protected function parseHtml( $html ) {
		libxml_use_internal_errors( true );

		$doc = new DOMDocument();
		$doc->loadHTML( $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );

		libxml_clear_errors();

		$xpath = new DOMXPath( $doc );

		// Find h1.gb-volume-title
		$nodes = $xpath->query( "//h1[contains(concat(' ', normalize-space(@class), ' '), ' gb-volume-title ')]" );
		if ( $nodes->length === 0 ) {
			return [ 'publication' => '', 'date' => '' ];
		}

		$h1 = $nodes->item( 0 );

		// Extract the <span> text (date) and then remove it from h1
		$spans = $h1->getElementsByTagName( 'span' );
		$rawDate = '';
		if ( $spans->length > 0 ) {
			$rawDate = $spans->item( 0 )->textContent;
			// Remove the span from h1 so it doesn't appear in the publication title
			$h1->removeChild( $spans->item( 0 ) );
		}

		$publication = trim( $h1->textContent );

		// Format the date (input like "Jan 1, 2020" -> "January 1, 2020")
		$timestamp = strtotime( $rawDate );
		if ( $timestamp !== false ) {
			$date = date( 'F j, Y', $timestamp );
		} else {
			$date = $rawDate;
		}

		return [ 'publication' => $publication, 'date' => $date ];
	}

	/**
	 * Format the citation wikitext from the URL and parsed metadata.
	 *
	 * Mirrors the Node.js citation_text() function.
	 *
	 * @param string $url
	 * @param string $publication
	 * @param string $date
	 * @return string
	 */
	protected function formatCitation( $url, $publication, $date ) {
		$text = '{{' . $publication;

		// Parse query string from URL
		$queryString = preg_replace( '/^.*\?/', '', $url );
		$queryString = preg_replace( '/#.*$/', '', $queryString );
		$parts = explode( '&', $queryString );

		foreach ( $parts as $part ) {
			if ( strpos( $part, 'id=' ) === 0 ) {
				$text .= "\n| " . $part;
			} elseif ( strpos( $part, 'pg=' ) === 0 ) {
				if ( preg_match( '/^pg=(.+[^0-9])([0-9]+)$/', $part, $pieces ) ) {
					if ( $pieces[1] !== 'PA' ) {
						$text .= "\n| page_prefix=" . $pieces[1];
					}
					$text .= "\n| page=" . $pieces[2];
				}
			}
		}

		$text .= "\n| title=";
		$text .= "\n| date=" . $date;
		$text .= "\n}}";

		return $text;
	}
}
