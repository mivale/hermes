<?php

namespace Hermes\Server;

class Auth {

	/**
	 * get the given API key from the request
	 * @return string
	 */
	public static function getRequestApiKey() {
		return @$_SERVER['HTTP_X_HERMES_API_KEY'];
	}
	
}