<?php
namespace Hermes\Server\Mail\Transport;

class Debug {
	public function name() {
		return 'debug()';
	}
	public function send($to, $subject, $body, $headers, $extra_params) {
		error_log(print_r(array($to, $subject, $body, $headers, $extra_params),true), 1, 'leening@dmmw.nl');
		return true;
	}
}