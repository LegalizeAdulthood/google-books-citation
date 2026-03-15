<?php
// SPDX-License-Identifier: GPL-2.0-only

namespace GoogleBooksCitation\Tests;

use ApiBase;
use ApiGoogleBooksCitation;
use ApiMain;
use ApiTestCase;
use ApiUsageException;

/**
 * @group API
 * @group Database
 * @group GoogleBooksCitation
 * @group medium
 * @covers ApiGoogleBooksCitation
 */
class ApiGoogleBooksCitationTest extends ApiTestCase {

	/**
	 * Test API requires URL parameter
	 */
	public function testExecuteMissingUrl() {
		$this->expectException( ApiUsageException::class );

		$this->doApiRequest( [
			'action' => 'googlebooks-citation'
		] );
	}

	/**
	 * Test getAllowedParams returns correct structure
	 */
	public function testGetAllowedParams() {
		$api = new ApiGoogleBooksCitation( new ApiMain(), 'googlebooks-citation' );
		$params = $api->getAllowedParams();

		$this->assertArrayHasKey( 'url', $params );
		$this->assertTrue( $params['url'][ApiBase::PARAM_REQUIRED] ?? false );
		$this->assertEquals( 'string', $params['url'][ApiBase::PARAM_TYPE] );
	}

	/**
	 * Test needsToken returns false
	 */
	public function testNeedsToken() {
		$api = new ApiGoogleBooksCitation( new ApiMain(), 'googlebooks-citation' );

		$this->assertFalse( $api->needsToken() );
	}

	/**
	 * Test isInternal returns false
	 */
	public function testIsInternal() {
		$api = new ApiGoogleBooksCitation( new ApiMain(), 'googlebooks-citation' );

		$this->assertFalse( $api->isInternal() );
	}
}
