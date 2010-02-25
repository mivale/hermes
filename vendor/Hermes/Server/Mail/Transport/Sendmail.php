<?php
namespace Hermes\Server\Mail\Transport;

class Sendmail {
	public function name() {
		return 'mail()';
	}
	public function send($to, $subject, $body, $headers, $extra_params) {
		return mail($to, $subject, $body, $headers, $extra_params);
	}
}