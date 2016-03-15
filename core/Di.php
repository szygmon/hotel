<?php

class Di {

	/**
	 * Di container
	 * @var array 
	 */
	private static $container = array();

	/**
	 * Mapped Class
	 * @var array 
	 */
	private static $map = array();

	/**
	 * File path to mapped class 
	 * @var array 
	 */
	private static $path = array();

	public static function get($name, $ifNull = null) {
		return array_key_exists($name, self::$container) ? self::$container[$name] : $ifNull;
	}

	public static function set($className, $name = false, $withPath = false) {
		if (is_object($className)) {
			if (!$name)
				$name = get_class($className);

			return self::$container[$name] = $className;
		} elseif (!class_exists($className)) {
			throw new Exception("Di: missing class: '" . $className . "'.");
		}

		if (!$name)
			$name = basename(str_replace('\\', '/', $className));

		if (isset(self::$map[$name])) {
			$arguments = array();
			$reflection = new ReflectionClass($className);
			if (isset(self::$map[$name]['__construct'])) {
				foreach (self::$map[$name]['__construct']['parameters'] as $parameter){
					$injected = Di::get($parameter['name']);
					$arguments[$parameter['name']] = $injected ? $injected : Di::set($parameter['name']);
				}
			}

			$class = $reflection->newInstanceArgs($arguments);
		} else
			$class = new $className;

		return self::$container[$name] = $class;
	}

	public static function mapClass($name) {
		if (!class_exists($name)) {
			throw new Exception("DI: map missing class '" . $name . "'.");
		}

		$className = basename(str_replace('\\', '/', $name));
		$reflection = new ReflectionClass($name);
		foreach ($reflection->getMethods() as $method) {
			self::$map[$className][$method->name]['parameters'] = array();

			foreach ($method->getParameters() as $parameter) {
				self::$map[$className][$method->name]['parameters'][$parameter->getName()] = array('name' => $parameter->getName());
				if ($parameter->isOptional())
					self::$map[$className][$method->name]['parameters'][$parameter->getName()] += array('default' => $parameter->getDefaultValue());
			}

			if ($doc = $method->getDocComment()) {
				preg_match('#@Route\("?/?(([^"]*))"?\)#', $doc, $match);
				if (isset($match[1]))
					self::$map[$className][$method->name]['route'] = $match[1];
			}
		}

		self::$path[$className] = $reflection->getFileName();
	}

	public static function getMap($class = false) {
		if ($class)
			return self::$map[$class];
		return self::$map;
	}

	public static function getPath($class) {
		if (isset(self::$path[$class]))
				return self::$path[$class];
		return false;
	}

	public static function init() {
		foreach (glob(ABSPATH . WEB . 'Controller/*.*') as $class) {
			self::mapClass('Controller\\' . basename($class, '.php'));
		}
	}

}
