<?php

namespace Controller;

use Core\Response;

class User {

	/** @var \User\Me */
	protected $me;

	/** @var \Doctrine\ORM\EntityManager */
	protected $em;

	/** @var \UserUtil */
	protected $userUtil;

	/**
	 * @param \User\Me $Me
	 * @param \Doctrine\ORM\EntityManager $em
	 */
	function __construct($Me, $em, $UserUtil) {
		$this->me = $Me;
		$this->em = $em;
		$this->userUtil = $UserUtil;
	}

	/**
	 * edit user profile
	 * @Route("/profile/edit/{id}")
	 */
	public function edit($id = null) {
		if ($id)
			$user = $this->em->getRepository('Model\\User')->find($id);
		else
			$user = $this->me->getModel();
		$this->userUtil->edit($user, $_POST);

		return array('user' => $user);
	}

}
