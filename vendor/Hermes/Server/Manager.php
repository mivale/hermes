<?php

namespace Hermes\Server;
use Hermes\Server\DB;

class Manager{
	
	protected $db;
	protected $user;
	
	function __construct(DB $db, $user) {
		$this->setDb($db);
		$this->setUser($user);
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

}