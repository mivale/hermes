<?php

namespace Hermes\Server;
use Hermes\Server\Manager;

class UserManager extends Manager {
		
	function __construct(DB $db) {
		$this->setDb($db);
	}
	
	public function find($apikey) {
		return $this->db->findRowBy('user', 'key = '.$this->db->quote($apikey));
	}
	
}