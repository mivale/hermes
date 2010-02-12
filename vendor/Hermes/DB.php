<?php

/**
 * @author leening
 *
 */
class Hermes_DB {
	protected $dbh;
	
	public function __construct($dbfile) {
		$this->dbh = new PDO('sqlite:' . $dbfile);
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
		foreach ( $this->dbh->query('SELECT * FROM '.$table.' WHERE '.$where,  PDO::FETCH_OBJ) as $row ) {
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
}