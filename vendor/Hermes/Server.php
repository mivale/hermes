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
	protected $DB;
	protected $client;
	
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
	
	/**
	 * @param array $post
	 * @return string
	 */
	private function _sendBatch($post = null) {
		$result = array('code'=> 501, 'message' => 'Not Implemented');
		$this->_sendError($result);
	}
	
	/**
	 * @param array $post
	 * @return string
	 */
	private function _createRun($post = null) {
		include_once('library/lib.uuid.php');
		$uuid = UUID::mint();
		if ($uuid) {
			$result = array('code'=> 202, 'message' => 'Run initialized');
			$result['run-id'] = $uuid->string;

			/**
			 * add all known given tags to the response - these tags can be used by the client
			 */
			
			if ($this->_validateTags($this->_findTags(), $post['tags'])) {
				$this->_sendOk($result);
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
		// client is validated, and record has already been loaded
		// now go and search the database
		$this->client->tags = $this->DB->findAllRowsBy('tag', 'klant_id = '.$this->client->id);
		return $this->client->tags;
	}
	
	/**
	 * Validate current clients tags 
	 * 
	 * @param array $tags array of tag uuids to search for
	 * @return array $tags the found tags as an associative array
	 */
	private function _validateTags($client_tags, $posted_tags) {
		// check all available keys
		foreach ($client_tags as $row) {
			// if the client posted this tag
			if (isset($posted_tags[$row->ident])) {
				// check if the uuid matches
				if ($posted_tags[$row->ident] != $row->tag_id) {
					return false;
				}
			}
		}
		return true;
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