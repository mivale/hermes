<?php

namespace Hermes\Server;
use Hermes\Server\RunManager;
use Hermes\Server\Exception;
use Hermes\Server\Mail;

class MailManager {
	
	protected $run;
	protected $runmanager;
	protected $bounceMailbox = 'bounces+%1$s+%2$s@bounces.hermes.dmmw.nl';

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
	 * TODO: refactor this to separate Mail class?
	 * 
	 * @param string $runid
	 * @param object $postbody
	 */
	public function add($runid, $postbody) {
		if (is_null($this->run)) {
			$this->run = $this->runmanager->get($runid);
		}
		if (empty($this->run->run_id)) {
			throw new Exception('Run with id '.$runid.' not found', 404);
		}
		// holds any errors
		$errors = array();
		foreach ($postbody['mails'] as $mail) {
			
			try {
				$inserted = $this->save($mail);
				$queue = new Mail($mail, sprintf($this->bounceMailbox, $inserted, $this->run->run_id));
				$queue->setTransport(new Mail\Transport\Debug());
				try {
					$queue->send();
				} catch (Exception $e) {
					throw new Exception('Email not sent', 500, $e->getResults());
				}
			} catch (Exception $e) {
				$errors[] = $e->getResults();
			}
		}
		// check $errors
		if (count($errors)) {
			throw new Exception('(some) mails were not added', 500, $errors);
		}
	}
	
	/**
	 * Store an email in the database
	 * 
	 * @param array $data
	 * @return int $inserted
	 * @throws Hermes\Server\Exception
	 */
	protected function save(array $data) {
		$mail = array(
			'id' => null,
			'run_id' => $this->run->run_id,
			'uniq' => $data['uniq'],
			'headers' => json_encode($data['headers']),
			'body' => $data['body']
		);
		$inserted = $this->runmanager->getDb()->insertRow('mail', $mail);
		if (!$inserted) {
			throw new Exception('Error saving mail', 500, array(
				'uniq' => $data['uniq'],
				'headers' => $data['headers'],
				'code' => 500,
				'message' => 'Not inserted'
			));
		} else {
			return $inserted;
		}
	}

	public function validate() {
		return true;
	}
	
}