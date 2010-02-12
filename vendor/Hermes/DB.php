<?php

/**
 * @author leening
 *
 */
class Hermes_DB {
	protected $dbh;
	
	public function __construct($dbfile) {
		$this->dbh = new PDO ( 'sqlite:' . $dbfile );
	}
	
	public function findAllRows($table) {
		$rows = array();
		foreach ( $this->dbh->query('SELECT * FROM '.$table,  PDO::FETCH_OBJ ) as $row ) {
			$rows[] = $row;
		}
		return $rows;
	}
	
	public function findAllRowsBy($table, $where) {
		$rows = array();
		foreach ( $this->dbh->query('SELECT * FROM '.$table,' WHERE '.$where,  PDO::FETCH_OBJ ) as $row ) {
			$rows[] = $row;
		}
		return $rows;
	}
}