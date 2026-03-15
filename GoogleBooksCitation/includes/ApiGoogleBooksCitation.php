<?php
// SPDX-License-Identifier: GPL-2.0-only

class ApiGoogleBooksCitation extends ApiBase {

	public function execute() {
		$url = $this->getParameter( 'url' );

		$citationParser = new GoogleBooksCitationParser();
		$result = $citationParser->buildCitation( $url );

		if ( $result === false ) {
			$this->dieWithError( 'googlebooks-citation-error-fetch' );
		}

		$this->getResult()->addValue( null, 'citation', $result );
	}

	/** @inheritDoc */
	public function getAllowedParams() {
		return [
			'url' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			],
		];
	}

	public function needsToken() {
		return false;
	}

	public function isInternal() {
		return false;
	}

	/** @inheritDoc */
	protected function getExamplesMessages() {
		return [
			'action=googlebooks-citation&url=https://books.google.com/books?id=example'
				=> 'apihelp-googlebooks-citation-example',
		];
	}
}
