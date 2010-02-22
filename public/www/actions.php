<?php
$front('*')->action(function ($con) {
    echo 'Are you lost?';
});

$front('/run')
	->action(function ($con) {
	})
	->get(function ($con) {
		$con->hermes->notImplemented();
	})
	->head(function ($con) {
		$con->hermes->notImplemented();
	})
	->post(function ($con) {
		$run_id = $con->runmanager->create($con->postbody);
		$con->hermes->success(array(
			'code' => 202,
			'run_id' => $run_id,
			'message' => 'Run initialized',
		));
	});

$front('/run/:runid')
	->action(function ($con) {
	})
	->get(function ($con) {
		$con->hermes->notImplemented();
	})
	->head(function ($con) {
		$con->hermes->notImplemented();
	})
	->post(function ($con) {
		// $con->hermes->notImplemented();
		var_dump($con->runid, $con->postbody);
	});
