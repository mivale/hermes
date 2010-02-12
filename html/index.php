<?php
/**
 * @author Michiel van Leening <leening@dmmw.nl>
 */

//var_dump($_SERVER); exit;

set_include_path(realpath(__DIR__.'/..'));

require_once('vendor/Hermes/Server.php');

$server = new Hermes_Server();
$server->accept();