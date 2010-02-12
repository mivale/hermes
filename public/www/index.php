<?php
/**
 * @author Michiel van Leening <leening@dmmw.nl>
 */

//var_dump($_SERVER); exit;

define('BASEPATH', realpath(__DIR__.'/../..'));
set_include_path(BASEPATH);

require_once('vendor/Hermes/Server.php');
require_once('vendor/Hermes/DB.php');

$DB = new Hermes_DB(BASEPATH.'/data/hermes.db');

$server = new Hermes_Server();
$server->setDB($DB);
$server->accept($_SERVER['SCRIPT_URL']);