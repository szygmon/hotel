<?php

namespace Core;

class Autoloader {

	/**
	 * Pathes container 
	 * @var string[]
	 */
	private static $files = array();

	/**
	 * Registered namespaces
	 * @var string[]
	 */
	private static $namespaces = array();

	public static function register($prepend = false) {
		spl_autoload_register(array(__CLASS__, 'autoload'), true, $prepend);
	}

	public static function addFile($class, $path) {
		if (!is_file($path))
			return false;

		self::$files[$class] = $path;
		return true;
	}

	public static function registerNamespaces(array $namespaces) {
		self::$namespaces += $namespaces;
	}

	public static function autoload($class) {
		$baseDir = str_replace('\\', '/', $class);
		$file = basename($baseDir);

		foreach (self::$files as $c => $path) {
			if ($class === $c) {
				require $path;
				return true;
			}
		}

		foreach (self::$namespaces as $namespace => $path) {
			if ((is_numeric($namespace) || 0 === strpos($class, $namespace)) && is_file($filePath = $path . $file . '.php')) {
				require $filePath;
				return true;
			}
		}

		return false;
	}

}
