<?php

namespace Core;

class Http {

	public static function redirect($url) {
		Cookie::destruct();
		header("location: " . $url);
		exit();
	}

	public static function isAjax() {
		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
	}

}
