<?php

use \Model\School;

class DevReset {

	/** @var \Doctrine\ORM\EntityManager */
	protected $em;

	public function __construct() {
		$this->em = Di::get('em');
	}

	public function create() {
		Db::exec('set search_path to public');
		return Db::install('public');
	}

	public function update() {
		$schools = $this->em->getRepository('Model\\School')->findAll();
		$um = new UserManager;
		$r = array();
		foreach ($schools as $school) {
			$r += $um->setup($school);
		}
		return $r;
	}

	public function dummyData() {
		$userManager = new UserManager;
		
		// School
		$school = new School();
		$school->setAlias('test');
		$school->setName('Szkoła testowa');
		$this->em->persist($school);
		$r = $userManager->setup($school);
		if (count($r))
			return $r;

		// Role
		$adminRole = new \Model\Role;
		$adminRole->setName('admin');
		$this->em->persist($adminRole);
		
		// Admin
		$user = new \Model\Teacher();
		$user->setEmail('admin@put.poznan.pl');
		$user->setUsername('admin');
		$user->setPassword('qwerty');
		$user->setGivenName('Admin');
		$user->setFamilyName('Test');
		$user->addRole($adminRole);
		$this->em->persist($user);
		$this->em->flush();
		
		// Teacher
		$user = new \Model\Teacher();
		$user->setEmail('teacher@put.poznan.pl');
		$user->setUsername('teacher');
		$user->setPassword('qwerty');
		$user->setGivenName('Teacher');
		$user->setFamilyName('Test');
		$this->em->persist($user);
		$this->em->flush();

		// U1
		$user = new \Model\Student;
		$user->setEmail('adamski@put.poznan.pl');
		$user->setUsername('adamski');
		$user->setPassword('qwerty');
		$user->setGivenName('Adrian');
		$user->setFamilyName('Adamski');
		$this->em->persist($user);
		$this->em->flush();
		
		// U2
		$user = new \Model\Student;
		$user->setEmail('michalewicz@put.poznan.pl');
		$user->setUsername('michalewicz');
		$user->setPassword('qwerty');
		$user->setGivenName('Szymon');
		$user->setFamilyName('Michalewicz');
		$this->em->persist($user);
		$this->em->flush();
		
		return $r;
	}

}
