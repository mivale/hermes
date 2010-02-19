<?php
/**
 * @author Michiel van Leening <leening@dmmw.nl>
 */

//var_dump($_SERVER); exit;

define('BASEPATH', realpath(__DIR__.'/../..'));
set_include_path(get_include_path(). PATH_SEPARATOR. BASEPATH.'/library'. PATH_SEPARATOR. BASEPATH.'/vendor');

use snap\Snap, snap\Loader, snap\Front;

// setup the autoloader
require_once('Zend/Loader/Autoloader.php');
$loader = Zend_Loader_Autoloader::getInstance();
$loader->setFallbackAutoloader(true);

/*
try {
	Auth::
	
} catch(Exception $) {
	
	die;
}

*/

/////

$front = Front::getInstance();

$front('*')->action(function ($con) {
    echo 'Are you lost?';
});

$front('/run')
	->action(function ($con) {
	    $con->hermes = new Hermes_Server();
	    $con->hermes->setDB(new Hermes_DB(BASEPATH.'/data/hermes.db'));
	    $con->hermes->setFrontController($con->front);
	})
	->get(function ($con) {
		$con->hermes->notImplemented();
		// echo 'post to create a run';
	})
	->post(function ($con) {
		$con->hermes->createRun();
	});

$front('/run/:runid')
	->action(function ($con) {
	    $con->hermes = new Hermes_Server();
	    $con->hermes->setDB(new Hermes_DB(BASEPATH.'/data/hermes.db'));
	    $con->hermes->setFrontController($con->front);
	})
	->get(function ($con) {
		$con->hermes->notImplemented();
	})
	->post(function ($con) {
	});

$front->returnResponse(true);
$response = $front->dispatch();

$response->sendResponse();

//var_dump($response);

//$r = $front->getRequest();
//$r->setHttpResponseCode(501);
//$front->dispatch();
//$response = $front->getResponse();
exit;

require_once('vendor/Hermes/Server.php');
require_once('vendor/Hermes/DB.php');

$DB = new Hermes_DB(BASEPATH.'/data/hermes.db');

$server = new Hermes_Server();
$server->setDB($DB);
$server->accept($_SERVER['SCRIPT_URL']);