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

$client = new Hermes_Client();

$client->set_apiKey('893e4410-14bd-11df-8df2-7b90f7c29d12');
$client->set_serverUrl('http://hermes.leening.nas/');

$client->debug(Hermes_Client::DEBUG_VERBOSE);
$client->connect();

class Hermes_Client {
	const DEBUG_OFF = 0;
	const DEBUG_VERBOSE = 1;
	const DEBUG_RETURN = 2;
	
	private $_debugMode = self::DEBUG_OFF;
	private $_apiKey;
	private $_serverUrl = '';
	
	/**
	 * Initialize
	 */
	public function __construct($apiKey = null) {
		if (!is_null($apiKey)) {
			$this->set_apiKey($apiKey);
		}
	}
	
	/**
	 * Connect to the the server and fetch a new run-id
	 * @return Hermes_Client
	 */
	public function connect() {
		$this->_send(array('method'=>'createRun'));
		return $this;
	}
	
	/**
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
	 * Turns debug output on
	 * 
	 * @param int $mode One of the debug constants
	 * @return Hermes_Client
	 */
	public function debug($mode = self::DEBUG_VERBOSE) {
		$this->_debugMode = $mode;
		return $this;
	}
	
	/** private methods **/
	
	/**
	 * Prepares the data array
	 */
	private function _prepareData() {
		$data = array ( );
		return $data;
	}
	
	/**
	 * @return Hermes_Client
	 */
	private function _send($data = array()) {
		
		$headers = array ('Accept: application/json', 'Content-Type: application/json', 'X-Hermes-Api-Key: ' . $this->get_apiKey() );
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->get_serverUrl() );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST' );
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data) );
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers );
		
		$return = curl_exec($ch);
		
		if ($this->_debugMode == self::DEBUG_VERBOSE) {
			echo "JSON: " . json_encode ( $data ) . "\nHeaders: \n\t" . implode ( "\n\t", $headers ) . "\nReturn:\n$return";
		} else if ($this->_debugMode == self::DEBUG_RETURN) {
			return array ('json' => json_encode ( $data ), 'headers' => $headers, 'return' => $return );
		}
		
		if (curl_error($ch) != '') {
			throw new Exception(curl_error($ch));
		}
		
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		
		if (! $this->_isTwoHundred ($httpCode)) {
			$message = json_decode ( $return )->message;
			throw new Exception ( "Error while sending data. Hermes returned HTTP code $httpCode with message \"$message\"" );
		}
		
		return $this;
	}
	
	/**
	 * If a number is 200-299
	 */
	private function _isTwoHundred($value) {
		return intval ( $value / 100 ) == 2;
	}
	
	/**
	 * Validates an e-mailadress
	 */
	private function _validateAddress($email) {
		// http://php.net/manual/en/function.filter-var.php
		return filter_var ( $email, FILTER_VALIDATE_EMAIL ) !== false;
	}
}