<?php
// config.php
return array(
	'database' => array(
		'adapter' => 'pdo_mysql',
		'params'	=> array(
			'host'	 => 'database2',
			'username' => 'hermes',
			'password' => 'H3Rm3z',
			'dbname'	 => 'hermes'
		)
	),
	'dsn' => 'sqlite:' . BASEPATH.'/data/hermes.db'
);