<?php

error_reporting(E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING);

define('ABSPATH', dirname(__FILE__) . '/');
define('NCORE', 'core/');

if (file_exists(ABSPATH . 'config.php')) {
	require ABSPATH . NCORE . 'Kernel.php';
	Kernel::init();
	Kernel::resolve();
} else
	die("ERROR (Invalid config path)");
