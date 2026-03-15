<?php
// SPDX-License-Identifier: GPL-2.0-only

namespace GoogleBooksCitation\Tests;

use GoogleBooksCitationParser;
use MediaWikiTestCase;
use ReflectionMethod;

/**
 * @group GoogleBooksCitation
 * @covers GoogleBooksCitationParser
 */
class GoogleBooksCitationParserTest extends MediaWikiTestCase {

	/**
	 * Test parseHtml extracts publication and date from valid HTML
	 */
	public function testParseHtmlComplete() {
		$html = <<<HTML
<!DOCTYPE html>
<html>
<body>
    <h1 class="gb-volume-title">
        Popular Science
        <span>Jan 1, 2020</span>
    </h1>
</body>
</html>
HTML;

		$parser = new GoogleBooksCitationParser();
		$method = new ReflectionMethod( GoogleBooksCitationParser::class, 'parseHtml' );
		$method->setAccessible( true );

		$publication = '';
		$date = '';
		$method->invoke( $parser, $html, $publication, $date );

		$this->assertEquals( 'Popular Science', $publication );
		$this->assertEquals( 'January 1, 2020', $date );
	}

	/**
	 * Test parseHtml with no span (no date)
	 */
	public function testParseHtmlNoDate() {
		$html = <<<HTML
<!DOCTYPE html>
<html>
<body>
    <h1 class="gb-volume-title">Test Publication</h1>
</body>
</html>
HTML;

		$parser = new GoogleBooksCitationParser();
		$method = new ReflectionMethod( GoogleBooksCitationParser::class, 'parseHtml' );
		$method->setAccessible( true );

		$publication = '';
		$date = '';
		$method->invoke( $parser, $html, $publication, $date );

		$this->assertEquals( 'Test Publication', $publication );
		$this->assertEquals( '', $date );
	}

	/**
	 * Test parseHtml with no gb-volume-title element
	 */
	public function testParseHtmlNoTitle() {
		$html = <<<HTML
<!DOCTYPE html>
<html>
<body>
    <h1>Not the right class</h1>
</body>
</html>
HTML;

		$parser = new GoogleBooksCitationParser();
		$method = new ReflectionMethod( GoogleBooksCitationParser::class, 'parseHtml' );
		$method->setAccessible( true );

		$publication = '';
		$date = '';
		$method->invoke( $parser, $html, $publication, $date );

		$this->assertEquals( '', $publication );
		$this->assertEquals( '', $date );
	}

	/**
	 * Test parseHtml with malformed HTML
	 */
	public function testParseHtmlMalformed() {
		$html = '<h1 class="gb-volume-title">Broken HTML<span>Mar 5, 2019</span></div>';

		$parser = new GoogleBooksCitationParser();
		$method = new ReflectionMethod( GoogleBooksCitationParser::class, 'parseHtml' );
		$method->setAccessible( true );

		$publication = '';
		$date = '';
		$method->invoke( $parser, $html, $publication, $date );

		$this->assertEquals( 'Broken HTML', $publication );
		$this->assertEquals( 'March 5, 2019', $date );
	}

	/**
	 * Test formatCitation with id and PA page
	 */
	public function testFormatCitationWithPAPage() {
		$url = 'https://books.google.com/books?id=abc123&pg=PA42';
		$publication = 'Popular Science';
		$date = 'January 1, 2020';

		$parser = new GoogleBooksCitationParser();
		$method = new ReflectionMethod( GoogleBooksCitationParser::class, 'formatCitation' );
		$method->setAccessible( true );

		$result = $method->invoke( $parser, $url, $publication, $date );

		$this->assertContains( '{{Popular Science', $result );
		$this->assertContains( '| id=abc123', $result );
		$this->assertContains( '| page=42', $result );
		$this->assertNotContains( 'page_prefix', $result );
		$this->assertContains( '| title=', $result );
		$this->assertContains( '| date=January 1, 2020', $result );
		$this->assertContains( '}}', $result );
	}

	/**
	 * Test formatCitation with non-PA page prefix
	 */
	public function testFormatCitationWithNonPAPage() {
		$url = 'https://books.google.com/books?id=abc123&pg=RA1-10';
		$publication = 'Test Journal';
		$date = 'March 15, 2019';

		$parser = new GoogleBooksCitationParser();
		$method = new ReflectionMethod( GoogleBooksCitationParser::class, 'formatCitation' );
		$method->setAccessible( true );

		$result = $method->invoke( $parser, $url, $publication, $date );

		$this->assertContains( '{{Test Journal', $result );
		$this->assertContains( '| id=abc123', $result );
		$this->assertContains( '| page_prefix=RA1-', $result );
		$this->assertContains( '| page=10', $result );
		$this->assertContains( '| date=March 15, 2019', $result );
	}

	/**
	 * Test formatCitation with id only (no page)
	 */
	public function testFormatCitationIdOnly() {
		$url = 'https://books.google.com/books?id=abc123';
		$publication = 'Test Book';
		$date = 'June 5, 2015';

		$parser = new GoogleBooksCitationParser();
		$method = new ReflectionMethod( GoogleBooksCitationParser::class, 'formatCitation' );
		$method->setAccessible( true );

		$result = $method->invoke( $parser, $url, $publication, $date );

		$this->assertContains( '{{Test Book', $result );
		$this->assertContains( '| id=abc123', $result );
		$this->assertNotContains( '| page=', $result );
		$this->assertNotContains( 'page_prefix', $result );
		$this->assertContains( '| title=', $result );
		$this->assertContains( '| date=June 5, 2015', $result );
	}

	/**
	 * Test formatCitation with URL containing fragment
	 */
	public function testFormatCitationUrlWithFragment() {
		$url = 'https://books.google.com/books?id=abc123&pg=PA10#v=onepage';
		$publication = 'Test Book';
		$date = 'July 1, 2018';

		$parser = new GoogleBooksCitationParser();
		$method = new ReflectionMethod( GoogleBooksCitationParser::class, 'formatCitation' );
		$method->setAccessible( true );

		$result = $method->invoke( $parser, $url, $publication, $date );

		$this->assertContains( '| id=abc123', $result );
		$this->assertContains( '| page=10', $result );
		$this->assertNotContains( 'v=onepage', $result );
	}

	/**
	 * Test formatCitation with multiple query params
	 */
	public function testFormatCitationIgnoresNonIdPgParams() {
		$url = 'https://books.google.com/books?id=abc123&lpg=PA1&dq=test&pg=PA42';
		$publication = 'Test Book';
		$date = 'January 1, 2020';

		$parser = new GoogleBooksCitationParser();
		$method = new ReflectionMethod( GoogleBooksCitationParser::class, 'formatCitation' );
		$method->setAccessible( true );

		$result = $method->invoke( $parser, $url, $publication, $date );

		$this->assertContains( '| id=abc123', $result );
		$this->assertContains( '| page=42', $result );
		$this->assertNotContains( 'lpg=', $result );
		$this->assertNotContains( 'dq=', $result );
	}
}
