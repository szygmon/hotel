<?php

use Core\Autoloader;

Autoloader::registerNamespaces([5 => ABSPATH . WEB . '/Util/']);

$template = Di::get('Router');
$sub = $template->getSubdomain();

if ($sub) {
	$um = new UserManager();
	$um->switchSchema('school_' . $sub);
}
	