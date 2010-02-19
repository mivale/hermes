<?php
/**
 * Hermes PHP Client class
 *
 * Copyright 2010, Michiel van Leening, DMM Websolutions, www.dmmw.nl
 *
 * @author Michiel van Leening (leening@dmmw.nl)
 * @copyright Copyright 2010, Michiel van Leening, DMM Websolutions, www.dmmw.nl
 * @version 0.1
 * @license Restricted
 *
 * Usage:
 * 
 * $client = new Hermes_Client('uuid-api-key');
 */

class Hermes_Client {
	
	const VERSION = 0.1;
	
	const DEBUG_OFF = 0;
	const DEBUG_VERBOSE = 1;
	const DEBUG_RETURN = 2;
	
	private $_debugMode = self::DEBUG_OFF;
	private $_apiKey;
	private $_serverUrl = '';
	private $_tags = array();
	
	public $result;
	
	/**
	 * Initialize
	 */
	public function __construct($apiKey = null, $tags = null) {
		if (!is_null($apiKey)) {
			$this->set_apiKey($apiKey);
		}
		if (!is_null($tags)) {
			$this->set_tags($tags);
		}
	}
	
	/**
	 * Connect to the the server and create a new run
	 * @return Hermes_Client
	 */
	public function createRun() {
		$data = array(
			'tags' => $this->get_tags()
		);
		$this->result = $this->_send('/run', $data);
		return $this;
	}
	
	/**
	 * Turns debug output on
	 * 
	 * @param int $mode One of the debug constants
	 * @return Hermes_Client
	 */
	public function debug($mode = self::DEBUG_VERBOSE) {
		$this->_debugMode = $mode;
		return $this;
	}
	
	/*** Getters & Setters ************************************************/
	
	/**
	 * @param $apiKey the $apiKey to set
	 * @return Hermes_Client
	 */
	public function set_apiKey($apiKey) {
		$this->_apiKey = $apiKey;
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function get_apiKey() {
		return $this->_apiKey;
	}
	
	/**
	 * @return the $_serverUrl
	 */
	public function get_serverUrl() {
		return $this->_serverUrl;
	}

	/**
	 * @param $_serverUrl the $_serverUrl to set
	 * @return Hermes_Client
	 */
	public function set_serverUrl($_serverUrl) {
		$this->_serverUrl = $_serverUrl;
		return $this;
	}

	/**
	 * @return the $_tags
	 * @return Hermes_Client
	 */
	public function get_tags() {
		return $this->_tags;
	}

	/**
	 * @param $_tags the $_tags to set
	 */
	public function set_tags($_tags) {
		$this->_tags = $_tags;
		return $this;
	}

	/*** Private methods **************************************************/
	
	/**
	 * Sends data to the server
	 * 
	 * @param array $data associative array which is sent to server as json
	 * 
	 * @return Hermes_Client
	 */
	private function _send($url, $data = array()) {
		
		$headers = array ('Accept: application/json', 'Content-Type: application/json', 'X-Hermes-Api-Key: ' . $this->get_apiKey() );
		
		$json = json_encode($data);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->get_serverUrl() . $url );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST' );
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json );
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers );
		
		$return = curl_exec($ch);
		
		if ($this->_debugMode == self::DEBUG_VERBOSE) {
			echo "JSON: " . $json . "\nHeaders: \n\t" . implode ( "\n\t", $headers ) . "\nReturn:\n$return";
		} else if ($this->_debugMode == self::DEBUG_RETURN) {
			return array ('json' => $json, 'headers' => $headers, 'return' => $return );
		}
		
		if (curl_error($ch) != '') {
			throw new Exception(curl_error($ch));
		}
		
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		
		if (! $this->_isTwoHundred ($httpCode)) {
			$message = json_decode ( $return )->message;
			throw new Exception ( "Error while sending data. Hermes returned HTTP code $httpCode with message \"$message\"" );
		}
		
		return $return;
	}
	
	/**
	 * If a number is 200-299
	 * 
	 * @param string $value returncode
	 * @return bool
	 */
	private function _isTwoHundred($value) {
		return intval ( $value / 100 ) == 2;
	}
	
	/**
	 * Validates an e-mailadress
	 * 
	 * @param string $email address to check
	 * @return bool
	 */
	private function _validateAddress($email) {
		// http://php.net/manual/en/function.filter-var.php
		return filter_var ( $email, FILTER_VALIDATE_EMAIL ) !== false;
	}
}