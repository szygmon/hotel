<?php

class Notify {

	/** @var array */
	private static $data = array();

	/**
	 * Shutdown function
	 * @return type
	 */
	public static function shutdown() {
		if (!self::$data)
			return;

		self::merge();
		return TRUE;
	}

	/**
	 * @param $type info/success/error
	 * @param $msg body
	 * @param $title title
	 */
	private static function add($type, $msg, $title) {
		if (!self::$data)
			register_shutdown_function('Notify::shutdown');
		self::$data[] = array('title' => $title, 'body' => $msg, 'type' => $type);
		return TRUE;
	}

	/**
	 * Add error
	 * @param string $msg
	 * @param string $title
	 * @return boolean
	 */
	public static function error($msg, $title = NULL) {
		return self::add('error', $msg, $title);
	}

	/**
	 * Add success
	 * @param string $msg
	 * @param string $title
	 * @return boolean
	 */
	public static function success($msg, $title = NULL) {
		return self::add('success', $msg, $title);
	}

	/**
	 * Add info
	 * @param string $msg
	 * @param string $title
	 * @return boolean
	 */
	public static function info($msg, $title = NULL) {
		return self::add('info', $msg, $title, $time);
	}

	/**
	 * Display notifications
	 * @return html
	 */
	public static function r() {
		self::merge();
		if (!isset($_SESSION['notify']))
			return;

		include __DIR__ . '/view/Notify.php';
		unset($_SESSION['notify']);
	}

	private static function merge() {
		$buf = $_SESSION['notify'];
		foreach (self::$data as $n)
			$buf[] = array('type' => $n['type'], 'title' => $n['title'], 'body' => $n['body']);

		$_SESSION['notify'] = $buf;
		self::$data = array();
	}

}
