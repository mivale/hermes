<?php
/**
 * Included from index.php
 */

$front('*')->action(function ($container) {
    echo 'Are you lost?';
});

$front('/run')
	->action(function ($container) {
	})
	->get(function ($container) {
		$container->hermes->notImplemented();
	})
	->head(function ($container) {
		$container->hermes->notImplemented();
	})
	->post(function ($container) {
		$run_id = $container->runmanager->create($container->postbody);
		$container->hermes->success(array(
			'code' => 202,
			'run_id' => $run_id,
			'message' => 'Run initialized',
		));
	});

$front('/run/:runid')
	->action(function ($container) {
	})
	->get(function ($container) {
		$container->hermes->notImplemented();
	})
	->head(function ($container) {
		$container->hermes->notImplemented();
	})
	->post(function ($container) {
		try {
			$container->mailmanager->add($container->runid, $container->postbody);
			$container->hermes->success(array(
				'code' => 202,
				'message' => 'Messages accepted',
			));
		} catch (Exception $e) {
			$container->response->setBody(json_encode(array('message'=>$e->getMessage(), 'code' => $e->getCode(), 'result' => false, 'mails' => $e->getResults())));
		}
	});
