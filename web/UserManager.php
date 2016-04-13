<?php

class UserManager {

	private $router;

	function __construct() {
		$this->router = Di::get('Router');
	}

	public function setup() {
		return Db::install();
	}

	public function switchSchema($school) {
		if (is_object($school))
			$school = $school->getSchema();

		$query = "set search_path to {$school}, public";
		Db::exec($query);
	}

}
