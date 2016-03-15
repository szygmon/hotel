<?php

use \Model\School;

class School {

	/** @var \User\Me */
	protected $Me;

	/**
	 * @param \User\Me $Me
	 */
	function __construct() {
		$this->Me = Di::get('Me');
	}

}
