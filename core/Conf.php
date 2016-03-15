<?php

class Conf {

	private static $conf;

	public static function init() {
		//  Load default config
		$conf = require_once ABSPATH . '/config.php';

		//  Add local config
		if (is_file(ABSPATH . '/local.php'))
			$conf = array_merge($conf, require_once ABSPATH . '/local.php');
		self::$conf = $conf;
	}

	public static function get($key = NULL, $ifNull = NULL) {
		if (is_null($key))
			return self::$conf;
		return array_key_exists($key, self::$conf) ? self::$conf[$key] : $ifNull;
	}

	public static function set($key, $val) {
		self::$conf[$key] = $val;
	}

}
