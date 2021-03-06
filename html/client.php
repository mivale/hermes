<?php
/**
 * @author Michiel van Leening <leening@dmmw.nl>
 */

define('BASEPATH', realpath(__DIR__.'/..'));
set_include_path(BASEPATH);

require_once('vendor/Hermes/Client.php');

$client = new Hermes_Client();

$client->set_apiKey('1b75df50-17b4-11df-b5c1-61856f3a2e36'); // this key will be created by DMM
$client->set_serverUrl('http://www.hermes.michiel.dmm-test');
$client->set_tags(array(
	'matchmail' => '1b763f80-17b4-11df-9313-c1e90bfb96df', // this tag will be created by DMM, only use approved tags
));
$client->debug(Hermes_Client::DEBUG_OFF);

try {
	$client->createRun();
} catch (Exception $e) {
	echo $e->getMessage();
	exit;
}

if ($client->isCreated()) {
	
	$testmails = array(
//		'run_id' => $client->get_runId(),
		'mails' => array()
	);
	
	foreach (range(1, 5) as $i) {
		$testmails['mails'][] = array(
			'uniq' => $i, // microtime(true).'/'.$i,
			'headers' => array(
				'From' => 'leening@dmmw.nl',
				'To' => 'michiel@dmmw.nl',
				'Subject' => 'Test time '.$i.' @ '.date('Y-m-d H:i:s')
			),
			'body' => 'Test message '.$i.' with timestamp '.date('Y-m-d H:i:s')
		);
	}
	
//	test breakage of apikey
//	$client->set_apiKey('1b5df50-17b4-11df-b5c1-61856f3a2e36'); // this key will be created by DMM
	
	try {
		$client->sendMail($testmails);
		echo($client->result_json);
	} catch (Exception $e) {
		echo $e->getMessage();
		exit;
	}
} else {
	// Error !
}
