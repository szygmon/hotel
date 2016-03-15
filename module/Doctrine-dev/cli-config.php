<?php

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use core\Autoloader;

require __DIR__ . '/../../core/Kernel.php';
Kernel::init();
$entityManager = Di::get('em');
//EntityManager::create($conn, $config);

return \Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet($entityManager);
