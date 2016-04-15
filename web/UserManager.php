<?php

class UserManager {

	private $router;

	function __construct() {
		$this->router = Di::get('Router');
	}

	public function setup() {
		return Db::install();
	}
}
