<?php

namespace Hermes;
use Exception;

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
 * Requires:
 * 		sqlite3
 * 		write access to the database + folder
 *
 * Usage:
 */

class Server {

	const VERSION = 0.1;

	protected $response;
	protected $contentType = 'text/html';

	/**
	 * Initialize
	 */
	public function __construct($response) {
		$this->setResponse($response);
	}
	
	/**
	 * @return the $response
	 */
	public function getResponse() {
		return $this->response;
	}

	/**
	 * @param $response the $response to set
	 */
	public function setResponse($response) {
		$this->response = $response;
	}

	/**
	 * @return the $contentType
	 */
	public function getContentType() {
		return $this->contentType;
	}

	/**
	 * @param $contentType the $contentType to set
	 * @return Server
	 */
	public function setContentType($contentType) {
		$this->contentType = $contentType;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public static function getPostBody() {
		$handle = fopen('php://input','r');
		$jsonInput = fgets($handle);
		return json_decode($jsonInput,true);
	}
	
	/**
	 * @param string $message
	 * @return Zend_Controller_Response_Abstract
	 */
	public function notImplemented($message='Not Implemented') {
		$result = array('code'=> 501, 'message' => $message);
		return $this->_sendError($result);
	}
	
	/**
	 * @param string $message
	 * @return Zend_Controller_Response_Abstract
	 */
	public function success(array $result) {
		return $this->_sendOk($result);
	}
	
	/**
	 * @return string
	 */
	private function _sendError(array $result) {
		$result['result'] = false;
		$this->getResponse()->appendBody(json_encode($result));
		throw new Exception($result['message'], $result['code']);
	}
	
	/**
	 * @return Zend_Controller_Response_Abstract
	 */
	private function _sendOk(array $result) {
		$result['result'] = true;
		return $this->_sendResult($result);
	}
	
	/**
	 * @return Zend_Controller_Response_Abstract
	 */
	private function _sendResult(array $result) {
		return $this->getResponse()->appendBody(json_encode($result));
	}

}