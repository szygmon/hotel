<?php

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use core\Autoloader;

Autoloader::addFile('Db', __DIR__ . '/Db.php');

$conn = array(
	'driver' => Conf::get('db.driver'),
	'host' => Conf::get('db.host'),
	'user' => Conf::get('db.user'),
	'password' => Conf::get('db.pass'),
	'dbname' => Conf::get('db.name'),
);

$config = Setup::createAnnotationMetadataConfiguration(array(ABSPATH . WEB . 'Model'), Conf::get('nc.debug'));
$entityManager = EntityManager::create($conn, $config);
Di::set($entityManager, 'em');