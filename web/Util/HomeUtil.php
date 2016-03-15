<?php

use \Model\School;

class HomeUtil {

	/** @var \User\Me */
	protected $Me;

	/** @var \Doctrine\ORM\EntityManager */
	protected $em;

	function __construct() {
		$this->Me = Di::get('Me');
		$this->em = Di::get('em');
	}

	public function registerForm($post) {
		$check = Di::get('em')->getRepository('\Model\School')->findOneBy(array('alias' => $_POST['schoolAlias']));
		if ($check)
			return \Notify::error('Alias zajęty!');
		
		$school = new School();
		// ++ unikalność

		$school->setName($_POST['schoolName']);
		$school->setAlias($_POST['schoolAlias']);
		(new \UserManager)->setup($school);

                // Role
		$adminRole = new \Model\Role;
		$adminRole->setName('admin');
		$this->em->persist($adminRole);
                
		$user = new \Model\User();
		$user->setUsername($_POST['username']);
		$user->setEmail($_POST['email']);
		$user->setPassword($_POST['pass']);
//		$user->setSchool($school);
                $user->addRole($adminRole);
		$this->em->persist($school);
		$this->em->persist($user);
		$this->em->flush();

		return \Notify::success('Zarejestrowano pomyślnie');	
	}

	public function loginForm($post) {
		$login = $this->Me->login($_POST['username'], $_POST['password']);

		if ($login) {
			$this->Me->getModel()->setLastLogin(new DateTime);
			Di::get('em')->flush();
			Di::get('Router')->redirect('Home/index');
		}

		return false;
	}

}
