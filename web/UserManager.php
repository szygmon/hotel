<?php

class UserManager {

	private $router;

	function __construct() {
		$this->router = Di::get('Router');
	}

	public function setup($school) {
		try {
			Db::exec('CREATE SCHEMA IF NOT EXISTS ' . $school->getSchema());
		} catch (Exception $e) {
			Db::exec('CREATE SCHEMA ' . $school->getSchema());
		}
		$this->switchSchema($school);
		return Db::install();
	}

	public function switchSchema($school) {
		if (is_object($school))
			$school = $school->getSchema();

		$query = "set search_path to {$school}, public";
		Db::exec($query);
	}

}
