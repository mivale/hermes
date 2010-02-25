<?php
/**
 * @author Michiel van Leening <leening@dmmw.nl>
 */

define('BASEPATH', realpath(__DIR__.'/../..'));
set_include_path(get_include_path(). PATH_SEPARATOR. BASEPATH.'/library'. PATH_SEPARATOR. BASEPATH.'/vendor');

use snap\Snap, snap\Loader, snap\Front;
use Hermes\Server, Hermes\Server\Config, Hermes\Server\DB;
use Hermes\Server\Auth, Hermes\Server\Exception;
use Hermes\Server\UserManager, Hermes\Server\RunManager, Hermes\Server\MailManager;

// setup the autoloader
require_once('Zend/Loader/Autoloader.php');
$loader = Zend_Loader_Autoloader::getInstance();
$loader->setFallbackAutoloader(true);

/*
$config = new Config(BASEPATH.'/library/config.php');
*/

$config = new \Zend_Config(require BASEPATH.'/library/config.php');

$front = Front::getInstance();

$container = $front->getContainer();

$container->apikey = Auth::getRequestApiKey();
$container->hermes = new Server($container->response);
//$container->hermes->setContentType('application/json');

$container->db = new DB($config->dsn);
$container->usermanager = new UserManager($container->db);
$container->user = $container->usermanager->find($container->apikey);

$container->runmanager = new RunManager($container->db, $container->user);
$container->mailmanager = new MailManager($container->runmanager);

try {
	$container->postbody = Hermes\Server::getPostBody();

	if (! $container->apikey) {
		throw new Exception('No API key received', 401);
	}
	if (!count($container->user)) {
		throw new Exception('Invalid key received', 412);
	}
	require('actions.php');
	$front->dispatch();
		
} catch(Exception $e) {
	$code = $e->getCode();
	if (empty($code)) {
		$code = 500;
	}
	
	$container->response->setHeader('Content-Type', $container->hermes->getContentType(),true);
//	$container->response->setRawHeader('401 No API key received');
	$container->response->setBody(json_encode(array('message'=>$e->getMessage(), 'code' => $code, 'result' => false)));
	$container->response->setHttpResponseCode($code);
	$container->response->sendResponse();

}
