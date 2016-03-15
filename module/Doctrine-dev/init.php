<?php

use core\Autoloader;

Autoloader::addFile('Controller\\Doctrine', __DIR__ . '/Controller/Doctrine.php');
Autoloader::addFile('DbPgsql', __DIR__ . '/DbPgsql.php');
Di::mapClass('Controller\\Doctrine');
