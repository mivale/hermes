<?php

namespace Hermes\Server;
use Hermes\Server\DB;

class UserManager {
	
	protected $db;
	
	function __construct(DB $db) {
		$this->setDb($db);
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
	
	public function find($apikey) {
		return $this->db->findRowBy('klant', 'key = '.$this->db->quote($apikey));
	}
	
}