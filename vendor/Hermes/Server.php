<?php
/**
 * Hermes PHP Server class
 *
 * Copyright 2010, Michiel van Leening, DMM Websolutions, www.dmmw.nl
 *
 * @author Michiel van Leening (leening@dmmw.nl)
 * @copyright Copyright 2010, Michiel van Leening, DMM Websolutions, www.dmmw.nl
 * @version 0.1
 * @license Restricted
 *
 * Usage:
 */

class Hermes_Server {
	
	/**
	 * Initialize
	 */
	public function __construct() {
	}
	
	/**
	 * @return void
	 */
	public static function accept() {
		var_dump($_SERVER);
	}
	
	/**
	 * get the given API key from the request
	 * @return string
	 */
	private function _getRequestApiKey() {
		return $_SERVER['HTTP_X_HERMES_API_KEY'];
	}
	
	/**
	 * check the validity of the API key
	 * @return string
	 */
	private function _isValidApiKey() {
		return true;
	}

}