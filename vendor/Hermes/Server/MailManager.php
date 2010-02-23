<?php

namespace Hermes\Server;
use Hermes\Server\RunManager;
use Hermes\Server\Exception;

class MailManager {
	
	protected $runmanager;
	
	private $run;

	public function __construct(RunManager $run) {
		$this->setRunmanager($run);
	}
	
	/**
	 * @return the $run
	 */
	public function getRunmanager() {
		return $this->runmanager;
	}

	/**
	 * @param $run the $runmanager to set
	 */
	public function setRunmanager($run) {
		$this->runmanager = $run;
		return $this;
	}
	
	/**
	 * 
	 * TODO: refactor this to separate Mail class
	 * 
	 * @param string $runid
	 * @param object $postbody
	 */
	public function add($runid, $postbody) {
		if (is_null($this->run)) {
			$this->run = $this->runmanager->get($runid);
		}
		if (empty($this->run->run_id)) {
			throw new \Exception('Run with id '.$runid.' not found', 404);
		}
		// holds any errors
		$result = array();
		foreach ($postbody['mails'] as $mail) {
			$inserted = $this->runmanager->getDb()->insertRow('mail', array(
				'id' => null,
				'run_id' => $this->run->run_id,
				'uniq' => $mail['uniq'],
				'headers' => json_encode($mail['headers']),
				'body' => $mail['body']
			));
			if (!$inserted) {
				$result[] = array(
					'uniq' => $mail['uniq'],
					'headers' => $mail['headers'],
					'code' => 500,
					'message' => 'Not inserted'
				);
			}
		}
		// check $result
		if (count($result)) {
			throw new Exception('(some) mails were not added', 500, $result);
		}
	}
}