<?php

use Core\Autoloader;
use Core\Cookie;

defined('ABSPATH') || define('ABSPATH', dirname(__FILE__) . '/../');
defined('NCORE') || define('NCORE', 'core/');
defined('WEB') || define('WEB', 'web/');

class Kernel {

	const VERSION = '1.0.0';

	private static $time;

	public static function init() {
		self::stopwatchStart();
		try {
			require ABSPATH . NCORE . 'Autoloader.php';
			require ABSPATH . NCORE . 'vendor/autoload.php';

			Autoloader::register();
			Autoloader::registerNamespaces(array(
				'Core' => ABSPATH . NCORE,
				'Controller' => ABSPATH . WEB . 'Controller/',
				'Model' => ABSPATH . WEB . 'Model/',
				ABSPATH . NCORE,
				ABSPATH . WEB,
			));

			if (version_compare(phpversion(), '5.3.0', '<'))
				throw new Exception('Invalid PHP version, required 5.3.0 or greater.');

			Conf::init();
			session_start();
			ob_start();

			ini_set('date.timezone', 'Europe/Warsaw');
			ini_set('short_open_tag', 1);
			ini_set('magic_quotes_gpc', 0);
	
			
			if (!Conf::get('nc.debug')) {
				ini_set('display_errors', 0);
				ini_set('error_reporting', -1);
			}
			Di::init();

//			if (!Conf::get('nc.debug'))
//				Di::set('core\Cache');
			Di::set('core\Router');
			Di::set('core\Template');

			// call modules
			$modules = glob(ABSPATH . 'module/*', GLOB_ONLYDIR);
			foreach ($modules as $module) {
				if (file_exists($module . '/init.php'))
					require $module . '/init.php';
			}
		} catch (PDOException $e) {
			echo "PDO error: " . $e->getMessage();
		} catch (Exception $e) {
			echo 'ERROR: ' . $e->getMessage();
		}
	}

	public static function resolve() {
		try {
			Di::get('Router')->render(true);
			Cookie::destruct();
			Di::get('Template')->render();
		} catch (Error404 $e) {
			$e->view();
		} catch (Error403 $e) {
			$e->view();
		} catch (PDOException $e) {
			echo "PDO error: " . $e->getMessage();
		} catch (Exception $e) {
			echo 'ERROR: ' . $e->getMessage();
		}
	}

	public static function version() {
		return self::VERSION;
	}

	public static function stopwatchStart() {
		self::$time['start'] = microtime(true);
		return true;
	}

	public static function stopwatchStop($display = 0, $precision = 3) {
		self::$time['stop'] = microtime(true);
		$timetotal = abs(self::$time['stop'] - self::$time['start']);
		$r = (function_exists('number_format_i18n')) ? number_format_i18n($timetotal, $precision) : number_format($timetotal, $precision);
		if ($display)
			echo $r;
		return $r;
	}

}
