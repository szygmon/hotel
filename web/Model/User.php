<?php

namespace Model;

use Doctrine\Common\Collections\ArrayCollection as Collection;

/**
 * @Entity()
 * @Table(name="users")
 * @InheritanceType("JOINED")
 * @HasLifecycleCallbacks()
 */
class User {

	use \AutoProperty;

	/**
	 * @Column(type="integer")
	 * @Id
	 * @GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/** @Column(type="string", unique=true) */
	protected $email;

	/** @Column(type="text", unique=true) */
	protected $username;

	/** @Column(type="string") */
	protected $password;

	/** @Column(type="string", nullable=true) */
	protected $givenName;

	/** @Column(type="string", nullable=true) */
	protected $familyName;

	/** @Column(type="boolean") */
	protected $isActive = true;

	/** @Column(type="datetime") */
	protected $ts;

	/** @Column(type="datetime", nullable=true) */
	protected $lastLogin;

	/** @Column(type="string", nullable=true) */
	protected $phone;

	/** @ManyToMany(targetEntity="\Model\Role") */
	protected $roles;

	/** @OneToMany(targetEntity="\Model\Notification", mappedBy="user") */
	protected $notifications;

	
	public function __construct() {
		$this->roles = new Collection;
	}
	
	/** @PrePersist */
	public function prePersist() {
		if (!isset($this->ts))
			$this->ts = new \DateTime;
	}

	/**
	 * Returns user authorization
	 * @param mixed $auth
	 * @return []
	 */
	public function auth($auth = null) {
		if ($auth)
			return \Me::auth($auth);
		$r = array();
		if (!$this->getId())
			return $r;
		if (!$this->getIsActive()) {
			$r[] = 'inactive';
			return $r;
		}

		$r[] = 'user';
		if ($this instanceof \Model\Teacher)
			$r[] = 'teacher';
		if ($this instanceof \Model\Student)
			$r[] = 'student';
		if ($this instanceof \Model\Parents)
			$r[] = 'parents';

		foreach ($this->roles as $role) {
			$r[] = $role->getName();
		}
		return $r;
	}

	public function setPassword($password) {
		$this->password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
		return $this;
	}
	
	public function addRole($role) {
		$this->roles->add($role);
	//	$team->addContract($this);
		return $this;
	}

}
