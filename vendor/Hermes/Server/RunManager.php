<?php

namespace Hermes\Server;
use Hermes\Server\DB;
use UUID;

class RunManager {
	
	protected $db, $user;
	
	function __construct(DB $db, $user) {
		$this->setDb($db);
		$this->setUser($user);
		// throw new Exception('RunManager not found', 404);
	}
	
	/**
	 * @param $db the $db to set
	 */
	public function setDb($db) {
		$this->db = $db;
		return $this;
	}

	/**
	 * @return the $db
	 */
	public function getDb() {
		return $this->db;
	}

	/**
	 * @param $user the $user to set
	 */
	public function setUser($user) {
		$this->user = $user;
		return $this;
	}

	/**
	 * @return the $user
	 */
	public function getUser() {
		return $this->user;
	}

	/**
	 * @param array $post
	 * @return string
	 */
	public function create($post = null) {
		$uuid = UUID::mint();
		if (!$uuid) {
			throw new Exception('Server error minting uuid');
		}
		//add all known given tags to the response - these tags can be used by the client
		if (!$this->_validateTags($this->_findTags(), $post['tags'])) {
			throw new Exception('Error in tag validation');
		}
		
		$run_id = $uuid->string;
		
		$sql_result = $this->db->insertRow('run', array('id'=>null,'runid'=>$run_id,'klant_id'=>$this->user->id));

		if (!$sql_result) {
			throw new Exception('Error saving run in database');
		}
			
		return $run_id;
	}

	/**
	 * Return tags from the DB for the currently connected client
	 * optionally filtering by given tags
	 * 
	 * @return array $tags the found tags as an associative array
	 */
	private function _findTags() {
		$this->user->tags = array();
		// client is validated, and record has already been loaded
		// now go and search the database
		$tags = $this->db->findAllRowsBy('tag', 'klant_id = '.$this->user->id);
		foreach ($tags as $row) {
			$this->user->tags[$row->ident] = $row->tag_id;
		}
		return $this->user->tags;
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
	
}
