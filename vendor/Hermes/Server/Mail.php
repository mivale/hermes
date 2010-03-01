<?php

namespace Hermes\Server;
use Hermes\Server\Exception;
use Hermes\Server\Mail\Transport;

class Mail {
	
	protected $message = array('headers' => '', 'body' => '');
	protected $envelope;
	protected $transport;
	
	public function __construct(array $message, $envelope = null) {
		$this->setMessage($message);
		if (!is_null($envelope)) {
			$this->setEnvelope($envelope);
		} else {
			$this->setEnvelope($message['headers']['From']);
		}
	}
	
	/**
	 * @return the $transport
	 */
	public function getTransport() {
		return $this->transport;
	}

	/**
	 * @param $transport the $transport to set
	 */
	public function setTransport($transport) {
		$this->transport = $transport;
		return $this;
	}

	/**
	 * @return the $envelope
	 */
	public function getEnvelope() {
		return $this->envelope;
	}

	/**
	 * Accepts an email address, and prepends it with a -f to pass on to mail()
	 * @param $envelope the $envelope to set
	 */
	public function setEnvelope($envelope) {
		$this->envelope = '-f'.$envelope;
		return $this;
	}

	/**
	 * @return the $message
	 */
	public function getMessage() {
		return $this->message;
	}

	/**
	 * @param $message the $message to set
	 */
	public function setMessage($message) {
		$this->message = $message;
		return $this;
	}
	
	public function send($transport = null) {
		if (is_null($transport)) {
            $transport = new Mail\Transport\Sendmail();
        }
		// create copy to manipulate
		$headers = $this->message['headers'];
		// remove To / Subject
		
		if (! $transport->send($headers['To'], $headers['Subject'], $this->message['body'], $this->_headersToString($this->_removeHeaders($headers)), $this->envelope)) {
			throw new Exception('Unable to send message using '.$transport->name(), 500, $this->message);
		}
	}
	
	private function _removeHeaders($headers) {
		return array_diff_key($headers, array('To' => true, 'Subject' => true));
	}
	
	private function _headersToString($headers) {
		$header_string = array();
		foreach ($headers as $key => $value) {
			$header_string[] = $key . ': ' . $value;
		}
		return join(PHP_EOL, $header_string);
	}
}