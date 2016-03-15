<?php

namespace Controller;

use \Conf;
use \Doctrine\ORM\Tools\SchemaTool;
use \Db;

class Doctrine {

	/** \Doctrine\ORM\EntityManager */
	protected $em;

	/**
	 * @param \Doctrine\ORM\EntityManager $em
	 */
	function __construct($em) {
		$this->em = $em;
	}

	/**
	 * @Route(/nc/db/dump/{token})
	 */
	public function dump($token) {
		if (Conf::get('cron.token') != $token)
			throw new \Error403;

		$schemaTool = new SchemaTool($this->em);
		$cmf = $this->em->getMetadataFactory();
		$sqls = $schemaTool->getUpdateSchemaSql($cmf->getAllMetadata(), false);

		return array('sqls' => $sqls);
	}

	/**
	 * @Route(/nc/db/reset/{token})
	 */
	public function reset($token) {
		if (Conf::get('cron.token') != $token)
			throw new \Error403;

		strpos(Conf::get('db.driver'), 'pgsql') !== FALSE ? \DbPgsql::dropAll() : \DbMysqli::dropAll();
		$r = $this->install();
		return array("error" => $r);
	}

	/**
	 * @Route(/nc/db/update/{token})
	 */
	public function update($token) {
		if (Conf::get('cron.token') != $token)
			throw new \Error403;

		$dev = new \DevReset;
		if (method_exists($dev, 'update'))
			$r = $dev->update();
		else
			$r = Db::install();
		return array("error" => $r);
	}

	public function install($data = true) {
		if (is_file(ABSPATH . WEB . 'DevReset.php')) {
			$dev = new \DevReset;
			$r = $dev->create();
			if (Conf::get('nc.debug') && $data)
				$r += $dev->dummyData();
		} else {
			$r = Db::install();
		}

		return $r;
	}

}
