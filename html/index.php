<?php

set_include_path(realpath(__DIR__.'/..'));

require_once('vendor/Hermes/Server.php');

$server = new Hermes_Server();
$server->accept();