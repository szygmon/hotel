<?php

use core\Autoloader;
use core\Cron;

define('ABSPATH', dirname(__FILE__) . '/');
define('NCORE', 'core/');
define('WEB', 'web/');

require_once (ABSPATH . NCORE . 'Autoloader.php');
Autoloader::register();
Conf::init();
Di::init();
Di::set('core\Model');

Cron::init($argv[1]);
