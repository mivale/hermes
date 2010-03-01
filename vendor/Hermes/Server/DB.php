<?php

namespace Hermes\Server;
use PDO;

/**
 * @author leening
 * 
 * Requires : php5
 *
 */
class DB {
	protected $dbh;
	
	public function __construct($database) {
		$this->dbh = new PDO($database->dsn, $database->user, $database->pass);
	}
	
	public function findAllRows($table) {
		$rows = array();
		foreach ( $this->dbh->query('SELECT * FROM '.$table,  PDO::FETCH_OBJ) as $row ) {
			$rows[] = $row;
		}
		return $rows;
	}
	
	public function findAllRowsBy($table, $where, $limit = null) {
		$rows = array();
		$query = 'SELECT * FROM '.$table.' WHERE '.$where;
		$result = $this->dbh->query($query,  PDO::FETCH_OBJ);
		foreach ( $result as $row ) {
			$rows[] = $row;
			if (!is_null($limit) && count($rows) == $limit) {
				if ($limit == 1) { return $row; }
				break;
			}
		}
		return $rows;
	}
	
	public function findRowBy($table, $where) {
		return $this->findAllRowsBy($table, $where, 1);
	}
	
	public function insertRow($table, $data) {
		$sth = $this->dbh->prepare('INSERT INTO '.$table.' ('.join(', ', array_keys($data)).') VALUES ('.join(', ', array_values(array_fill(0,count($data),'?'))).')');
		$result = $sth->execute(array_values($data));
		if (! $result) {
			return false;
		}
		$inserted_id = $this->_getInsertId();
		return $inserted_id;
	}
	
	public function updateRow($table, $primary, $data) {
		$pval = $this->dbh->quote($data[$primary]);
		unset($data[$primary]);
		$sth = $this->dbh->prepare('UPDATE '.$table.' SET '.join(', ', array_map(function ($key){ return "$key=?"; }, array_keys($data) )).' WHERE '.$primary.' = "'.$pval.'"');
		return $sth->execute(array_values($data));
	}
	
	public function quote($thing) {
		return $this->dbh->quote($thing);
	}
	
	public function query($thing) {
		return $this->dbh->query($thing);
	}
	
	/**
	 * This function needs to be overridden
	 * @return int
	 */
	protected function _getInsertId() {
		return $this->dbh->query('SELECT LAST_INSERT_ID();')->fetchColumn();
	}
	
}