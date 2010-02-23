<?php

namespace Hermes\Server;

class Exception extends \Exception {
	protected $results;

	public function __construct($message, $code, $results = null) {
		parent::__construct($message, $code);
		if (!is_null($results)) {
			$this->setResults($results);
		}
	}
	
	/**
	 * @param $results the $results to set
	 */
	public function setResults($results) {
		$this->results = $results;
		return $this;
	}

	public function getResults() {
		return $this->results;
	}
}