<?php

use Core\Autoloader;

Autoloader::addFile('Notify', __DIR__ . '/Notify.php');
Di::get('Template')->addTwigGlobals(['Notify' => new Notify]);
