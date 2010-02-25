<?php

namespace Hermes\Server;
use Hermes\Server\DB;
use Hermes\Server\Manager;
use UUID;
use Exception;

class RunManager extends Manager {
	
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
		
		// TODO: start transaction
		$inserted = $this->db->insertRow('run', array('id' => null, 'run_id' => $run_id, 'user_id' => $this->user->id));

		if (!$inserted) {
			throw new Exception('Error saving run in database');
		}
		
		// run created, save tags links
		foreach ($this->user->tags as $ident => $tag) {
			$tag_id = $this->db->query('SELECT id FROM tag WHERE tag_id = '.$this->db->quote($tag))->fetchColumn();
			// TODO: error checking?
			$this->db->insertRow('run_tag', array('run_id' => $inserted, 'tag_id' => $tag_id));
		}
		
		// TODO: commit transaction
		
		return $run_id;
	}
	
	public function get($runid) {
		return $this->db->findRowBy('run', 'run_id = '.$this->db->quote($runid));
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
		$tags = $this->db->findAllRowsBy('tag', 'user_id = '.$this->user->id);
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
