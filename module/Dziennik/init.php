<?php

use Core\Autoloader;

Autoloader::registerNamespaces([5 => ABSPATH . WEB . '/Util/']);

$template = Di::get('Router');

	