<?php

namespace Core;

use Conf;
use Di;

class Router {

	/**
	 * Route path
	 * @var string 
	 */
	private $path;

	/**
	 * Routes avaliable
	 * @var array 
	 */
	private $routeCollection;

	/** @var array */
	private $params;

	/** @var string */
	private $controller;

	/** @var string */
	private $subdomain = null;

	/** @var \Core\Response */
	private $response;

	function __construct() {
		if (Conf::get('nc.subdomains', false) && isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] != Conf::get('nc.site'))
			$this->subdomain = rtrim(str_replace(Conf::get('nc.domain'), '', $_SERVER['HTTP_HOST']), '.');
		if (isset($_SERVER['REQUEST_URI']))
			$this->path = trim(reset(explode('?', $_SERVER['REQUEST_URI'])), '/');
	}

	/**
	 * @return array
	 */
	public function getParams() {
		return $this->params;
	}

	/**
	 * @return string
	 */
	public function getSubdomain() {
		return $this->subdomain;
	}

	/**
	 * @return string
	 */
	public function getController() {
		return $this->controller;
	}

	/**
	 * @return \Core\Response
	 */
	public function getResponse() {
		return $this->response;
	}

	/**
	 * Generate routes
	 */
	public function generateRoutes() {
		$routes = array();
		foreach (Di::getMap() as $className => $class) {
			foreach ($class as $methodName => $method) {
				if (isset($method['route'])) {
					$routes[] = array(
						'controller' => $className . '/' . $methodName,
						'path' => $method['route'],
						'parameters' => $method['parameters'],
					);
				}
			}
		}

		usort($routes, function($a, $b) {
			return strcmp($a["path"], $b["path"]);
		});

		$this->routeCollection = $routes;
	}

	/**
	 * find route
	 * @return boolean
	 * @throws \Error404
	 */
	public function findRoute() {
		$match = $this->match($this->path);
		if ($match) {
			$this->controller = $match['route']['controller'];
			$this->params = $match['params'];
			return true;
		}

		throw new \Error404();
	}

	/**
	 * match url to route
	 * @param string $url
	 * @return array|boolean
	 */
	public function match($url) {
		foreach ($this->routeCollection as $route) {
			$pattern = $route['path'];
			foreach ($route['parameters'] as $parameter) {
				if (!Di::get($parameter['name'], false)) {
					$replacement = array_key_exists('default', $parameter) ? '(/[^/]+)?' : '(/[^/]+)';
					$pattern = preg_replace('(/?{' . $parameter['name'] . '})', $replacement, $pattern);
				}
			}

			if (preg_match("@^/?" . $pattern . "$@", '/' . $url, $params)) {
				unset($params[0]);
				foreach ($params as $k => $v) {
					$params[$k] = rawurldecode(trim($v, '/'));
				}

				return array('route' => $route, 'params' => $params);
			}
		}

		return false;
	}

	/**
	 * render controller
	 * @param boolean $full
	 */
	public function render($full = false) {
		if ($full) {
			$this->generateRoutes();
			$this->findRoute();
		}

		list($class, $method) = explode('/', $this->controller);
		$controllerObject = Di::set('Controller\\' . $class);
		$methodMap = Di::getMap($class)[$method];

		$arguments = array();
		$params = $this->params;
		foreach ($methodMap['parameters'] as $parameter) {
			$param = Di::get($parameter['name']);
			if (!$param) {
				$param = array_shift($params);
				if ($param === NULL && isset($parameter['default']))
					$param = $parameter['default'];
			}
			$arguments[$parameter['name']] = $param;
		}

		$response = call_user_func_array(array($controllerObject, $method), $arguments);

		if (is_array($response)) {
			$path = Di::getPath($class);
			if (strpos($path, realpath(ABSPATH . WEB)) === 0) {
				$path = false;
			} else {
				$path = realpath(dirname($path) . '/../View');
				$path = substr($path, strlen(ABSPATH));
			}
			$response = new Response($response, $this->controller, $path);
		}
		$this->response = $response;
	}

	/**
	 * Generate url's for controller
	 * @param string $controller
	 * @param array $args
	 * @param string $subdomain
	 * @param boolean $escaped
	 * @return string
	 * @throws \Exception
	 */
	public function url($controller, array $args = array(), $subdomain = null, $escaped = true) {
		if (is_null($subdomain))
			$subdomain = $this->subdomain;
		elseif ($subdomain === false) {
			$subdomain = trim(str_replace(Conf::get('nc.domain'), '', Conf::get('nc.site')), '.');
		}

		foreach ($this->routeCollection as $r) {
			if ($r['controller'] == $controller) {
				$route = $r;
				break;
			}
		}

		if (!isset($route))
			throw new \Exception('Brak takiego routingu: ' . $controller);

		$pattern = $route['path'];
		foreach ($args as $key => $arg) {
			$pattern = preg_replace('({' . $key . '})', $escaped ? rawurlencode($arg) : $arg, $pattern);
		}
		$pattern = preg_replace('(/?{.+})', '', $pattern);
		return rtrim($this->getUrl($subdomain) . '/' . $pattern, '/');
	}

	/**
	 * Return home site URL
	 * @param string $subdomain
	 * @return string
	 */
	public function getUrl($subdomain = null) {
		if (is_null($subdomain))
			$subdomain = $this->subdomain;

		return 'http' . (empty($_SERVER['HTTPS']) ? '' : 's') . '://'
				. (is_null($subdomain) || $subdomain === false ? Conf::get('nc.site') : $subdomain . '.' . ltrim(Conf::get('nc.domain'), '.'));
	}

	public function redirect($controller, array $args = array(), $subdomain = null, $escaped = true) {
		Cookie::destruct();
		header("location: " . $this->url($controller, $args, $subdomain, $escaped));
		exit;
	}

}
