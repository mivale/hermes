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
	public function accept() {
		$apikey = $this->_getRequestApiKey();
		if (empty($apikey)) {
			$this->_sendError(array('code'=> 401, 'message' => 'No API key received'));
		} else {
			if (!$this->_validateApiKey($apikey)) {
				$this->_sendResult(array('code'=> 412, 'message' => 'Invalid key received'));
			} else {
				/**
				 * TODO: differentiate between create/send/stats/etc.
				 * 
				 * very crude hack - should be converted to url recognitions
				 */
				$post = $this->_getPost();
				$method = '_public_'.$post['method'];
				if (isset($post['method']) && is_callable(array($this, $method))) {
					$this->$method($post);
				} else {
					$this->_createRun($post);
				}
			}
		}
	}
	
	private function _public_sendBatch($post = null) {
	}
	
	/**
	 * @return string
	 */
	private function _public_createRun($post = null) {
		include_once('library/lib.uuid.php');
		$uuid = UUID::mint();
		if ($uuid) {
			$result = array('code'=> 202, 'message' => 'Run initialized');
			$result['run-id'] = $uuid->string;
			$this->_sendOk($result);
		} else {
			$result = array('code'=> 500, 'message' => 'Server error minting uuid');
			$this->_sendError($result);
		}
	}
	
	/**
	 * @return mixed
	 */
	private function _getPost() {
		$handle = fopen('php://input','r');
		$jsonInput = fgets($handle);
		return json_decode($jsonInput,true);
	}
	
	/**
	 * @return string
	 */
	private function _sendResult(array $result) {
//		header('Content-Type: application/json');
		echo json_encode($result);
	}
	
	/**
	 * @return string
	 */
	private function _sendError(array $result) {
		$result['result'] = false;
		$this->_sendResult($result);
	}
	
	/**
	 * @return string
	 */
	private function _sendOk(array $result) {
		$result['result'] = true;
		$this->_sendResult($result);
	}
	
	/**
	 * @return string
	 */
	private function _validateApiKey($apikey) {
		return true;
	}
	
	/**
	 * get the given API key from the request
	 * @return string
	 */
	private function _getRequestApiKey() {
		return @$_SERVER['HTTP_X_HERMES_API_KEY'];
	}
	
	/**
	 * check the validity of the API key
	 * @return string
	 */
	private function _isValidApiKey() {
		return true;
	}

}