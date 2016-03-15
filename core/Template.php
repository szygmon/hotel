<?php

namespace Core;

use Di;
use Conf;

class Template {

	/**
	 * @var string
	 */
	private $template;

	/**
	 * @var \Core\Router 
	 */
	private $router;

	/**
	 * @var \Twig_Environment 
	 */
	private $twig = NULL;

	/**
	 * @var array
	 */
	private $twigFunctions = array();

	/**
	 * @var array
	 */
	private $twigGlobals = array();

	public function __construct() {
		$this->router = Di::get('Router');
		$this->template = Conf::get('nc.template', false);
		$this->twigFunctions = array(
			'url' => array($this, 'fnUrl'),
			'script' => array($this, 'fnScript'),
			'style' => array($this, 'fnStyle')
		);
	}

	/**
	 * @param string $template
	 */
	public function setTemplate($template) {
		$this->template = $template;
	}

	/**
	 * @return string
	 */
	public function getTemplate() {
		return $this->template;
	}

	public function addTwigFunctions(array $functions) {
		$this->twigFunctions += $functions;
	}

	public function addTwigGlobals(array $globals) {
		$this->twigGlobals += $globals;
	}

	private function getTemplatePath() {
		$path = WEB . 'View';
		return $this->template ? $path . '/' . $this->template : $path;
	}

	public function render(Response $response = NULL) {
		$debug = ob_get_clean();
		if ($debug && Conf::get('nc.debug')) {
			echo('<pre class="debug_dump">' . $debug . '</pre>');
		}

		if (!$response) {
			$response = $this->router->getResponse();
		}

		if ($response->getType() == 'text/html') {
			echo $this->renderView($response);
		} else
			echo $response->getData();
	}

	public function renderView(Response $response = NULL) {
		if (!$this->twig)
			$this->twig = $this->prepareTwig();
		$template = ($response->getPath() ? $response->getPath() : $this->getTemplatePath());
		$this->twig->getLoader()->setPaths(ABSPATH . $template . '/');
		return $this->twig->render($response->getTemplate() . '.html.twig', $response->getData());
	}

	private function prepareTwig() {
		$loader = new \Twig_Loader_Filesystem(ABSPATH);
		$twig = new \Twig_Environment($loader, array('cache' => ABSPATH . '/core/cache/twig/', 'debug' => Conf::get("nc.debug")));

		foreach ($this->twigFunctions as $name => $method) {
			$function = new \Twig_SimpleFunction($name, $method, array('is_safe' => array('html')));
			$twig->addFunction($function);
		}

		foreach ($this->twigGlobals as $name => $var)
			$twig->addGlobal($name, $var);

		$twig->addExtension(new \Twig_Extension_Core());
		if (Conf::get("nc.debug"))
			$twig->addExtension(new \Twig_Extension_Debug());
		$twig->addGlobal('Me', Di::get('Me'));
		return $twig;
	}

	public function fnUrl($route, $args = array(), $subdomain = null, $escaped = true) {
		if ($route === 'site')
			return $this->router->getUrl($subdomain || $subdomain === false ? $subdomain : Conf::get('nc.assets', null));
		if ($route === 'template')
			return $this->router->getUrl($subdomain || $subdomain === false ? $subdomain : Conf::get('nc.assets', null)) . '/' . $this->getTemplatePath();
		if ($route === 'libs')
			return $this->router->getUrl($subdomain || $subdomain === false ? $subdomain : Conf::get('nc.assets', null)) . '/core/libs/' . (is_string($args) ? $args : '');

		return $this->router->url($route, $args, $subdomain, $escaped);
	}

	public function fnScript($name = 'script') {
		return '<script src="' . $this->getAsset($name, 'js') . '" type="text/javascript"></script>';
	}

	public function fnStyle($name = 'style') {
		return '<link href="' . $this->getAsset($name, 'css') . '" rel="stylesheet" type="text/css" />';
	}

	public function getAsset($name, $type) {
		$file = glob(ABSPATH . NCORE . 'assets/' . ( $this->template ? $this->template . '/' : '' ) . $type . '/' . $name . '_v*.' . $type);
		if (is_null($file))
			return '';

		$file = str_replace(ABSPATH, '', $file[0]);
		return $this->router->getUrl(Conf::get('nc.assets', null)) . '/' . $file;
	}

}
