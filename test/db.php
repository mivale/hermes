<?php
/**
 * Please note: running this script will recreate test.db!
 */
include('../library/lib.uuid.php');

$dbfile = __DIR__ . '/test.db';
unlink($dbfile);

$dbh = new PDO ( 'sqlite:' . $dbfile );

$dbh->exec('
	CREATE TABLE klant (
		id INTEGER,
		key TEXT,
		naam TEXT,
		PRIMARY KEY (id)
	);
	CREATE TABLE tag (
		id INTEGER,
		klant_id INTEGER,
		tag_id TEXT,
		ident TEXT,
		PRIMARY KEY (id)
	);
');

$uuid = UUID::mint();
$dbh->exec('
	INSERT INTO klant VALUES (
		1,
		"'.$uuid->string.'",
		"DMM Websolutions BV"
	);
');

$uuid = UUID::mint();
$dbh->exec('
	INSERT INTO tag VALUES (
		1,
		1,
		"'.$uuid->string.'",
		"matchmail"
	);
');

foreach ( $dbh->query ( 'SELECT * FROM klant',  PDO::FETCH_OBJ ) as $row ) {
	print_r($row);
}

foreach ( $dbh->query ( 'SELECT * FROM tag',  PDO::FETCH_OBJ ) as $row ) {
	print_r($row);
}

/*
if ($db = new SQLiteDatabase ( __DIR__.'/test.db' )) {
	$q = @$db->query ( 'SELECT requests FROM tablename WHERE id = 1' );
	if ($q === false) {
		$db->queryExec ( 'CREATE TABLE tablename (id int, requests int, PRIMARY KEY (id)); INSERT INTO tablename VALUES (1,1)' );
		$hits = 1;
	} else {
		$result = $q->fetchSingle ();
		$hits = $result + 1;
	}
	$db->queryExec ( "UPDATE tablename SET requests = '$hits' WHERE id = 1" );
}
*/