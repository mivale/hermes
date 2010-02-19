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
 * Requires:
 * 		sqlite3
 * 		write access to the database
 *
 * Usage:
 */

class Hermes_Server {

	const VERSION = 0.1;
	
	protected $DB;
	protected $client;
	protected $frontController;
	protected $content_type = 'text/html';
	
	/**
	 * Initialize
	 */
	public function __construct($DB = null) {
		if (!is_null($DB)) {
			$this->setDB($DB);
		}
	}
	
	/**
	 * @return the $DB
	 */
	public function getDB() {
		return $this->DB;
	}

	/**
	 * @param $DB the $DB to set
	 */
	public function setDB($DB) {
		$this->DB = $DB;
		return $this;
	}

	/**
	 * @param string $url the url to handle
	 * @return void
	 */
	/*
	public function accept($url) {
		$apikey = $this->_getRequestApiKey();
		if (empty($apikey)) {
			$this->_sendError(array('code'=> 401, 'message' => 'No API key received'));
		} else {
			if (!$this->_validateApiKey($apikey)) {
				$this->_sendResult(array('code'=> 412, 'message' => 'Invalid key received'));
			} else {
				$post = $this->_getPost();
				switch ($url) {
					case '/create' :
						$this->_createRun($post);
						break;
					case '/batch' :
						$this->_sendBatch($post);
						break;
					default :
						// return error if json request else redirect homepage
						$this->_sendError(array('code'=> 501, 'message' => 'Not Implemented'));
						break;
				}
			}
		}
	}
	*/

	/**
	 * @return void
	 */
	public function createRun() {
		$apikey = $this->_getRequestApiKey();
		if (empty($apikey)) {
			$this->_sendError(array('code'=> 401, 'message' => 'No API key received'));
		} else {
			if (!$this->_validateApiKey($apikey)) {
				$this->_sendResult(array('code'=> 412, 'message' => 'Invalid key received'));
			} else {
				$post = $this->_getPost();
				$this->_createRun($post);
			}
		}
	}
	
	/**
	 * @param string $message
	 * @return string
	 */
	public function notImplemented($message='Not Implemented') {
		$result = array('code'=> 501, 'message' => $message);
		$this->_sendError($result);
	}
	
	public function setFrontController(Zend_Controller_Front $front) {
		$this->frontController = $front;
		return $this;
	}
	
	
	public function getFrontController() {
		return $this->frontController;
	}
	
	
	/**
	 * @param array $post
	 * @return string
	 */
	private function _sendBatch($post = null) {
		$this->notImplemented();
	}
	
	/**
	 * @param array $post
	 * @return string
	 */
	private function _createRun($post = null) {
		$uuid = UUID::mint();
		if ($uuid) {
			$result = array('code'=> 202, 'message' => 'Run initialized');
			$result['run-id'] = $uuid->string;

			/**
			 * add all known given tags to the response - these tags can be used by the client
			 */
			
			if ($this->_validateTags($this->_findTags(), $post['tags'])) {
				$sql_result = $this->DB->insertRow('run', array('id'=>null,'runid'=>$result['run-id'],'klant_id'=>$this->client->id));
				if ($sql_result) {
					$this->_sendOk($result);
				} else {
					$result = array('code'=> 500, 'message' => 'Error saving run in database');
					$this->_sendError($result);
				}
			} else {
				$result = array('code'=> 500, 'message' => 'Error in tag validation');
				$this->_sendError($result);
			}
		} else {
			$result = array('code'=> 500, 'message' => 'Server error minting uuid');
			$this->_sendError($result);
		}
	}
	
	/**
	 * Return tags from the DB for the currently connected client
	 * optionally filtering by given tags
	 * 
	 * @return array $tags the found tags as an associative array
	 */
	private function _findTags() {
		$this->client->tags = array();
		// client is validated, and record has already been loaded
		// now go and search the database
		$tags = $this->DB->findAllRowsBy('tag', 'klant_id = '.$this->client->id);
		foreach ($tags as $row) {
			$this->client->tags[$row->ident] = $row->tag_id;
		}
		return $this->client->tags;
	}
	
	/**
	 * Validate all given client tags
	 * Alle tags must validate 
	 * 
	 * @param array $tags array of tag uuids to search for
	 * @return array $tags the found tags as an associative array
	 */
	private function _validateTags($client_tags, $posted_tags) {
		foreach ($posted_tags as $ident => $uuid) {
			if (isset($client_tags[$ident]) && $client_tags[$ident] == $uuid) {
				return true;
			} else {
				return false;
			}
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
//		header('Content-Type: text/plain');
		$this->getFrontController()->getResponse()->setHeader('Content-Type',$this->content_type,true);
		$this->getFrontController()->getResponse()->setHttpResponseCode($result['code']);
		echo json_encode($result);
	}
	
	/**
	 * @return string
	 */
	private function _sendError(array $result) {
//		header('Status: '.$result['code'].' '.$result['message']);
		$result['result'] = false;
		$this->_sendResult($result);
	}
	
	/**
	 * @return string
	 */
	private function _sendOk(array $result) {
//		header('Status: '.$result['code']);
		$result['result'] = true;
		$this->_sendResult($result);
	}
	
	/**
	 * @return string
	 */
	private function _validateApiKey($apikey) {
		$this->client = $this->DB->findRowBy('klant', 'key = "'.$this->_getRequestApiKey().'"');
		return (isset($this->client->key) && $this->client->key == $apikey);
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