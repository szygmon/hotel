<?php

namespace Model;

use Doctrine\Common\Collections\ArrayCollection as Collection;

/**
 * @Entity
 */
class Student extends User {

	use \AutoProperty;

	/**
	 * @Column(type="string", length=45, nullable=true)
	 */
	protected $registrationNr;

	/**
	 * @Column(type="string", length=11, nullable=true) 
	 */
	protected $pesel;

	/**
	 * @Column(type="date", nullable=true)
	 */
	protected $birthdate;

	/**
	 * @Column(type="string", length=45, nullable=true) 
	 */
	protected $birthplace;

	/** @OneToMany(targetEntity="\Model\Rating", mappedBy="student") */
	protected $ratings;

	/** @OneToMany(targetEntity="\Model\Attendance", mappedBy="student") */
	protected $attendances;

	/**
	 * @ManyToMany(targetEntity="\Model\Group", inversedBy="students")
	 * @JoinTable(name="students_groups")
	 */
	protected $groups;

	/**
	 * @ManyToMany(targetEntity="\Model\Clas", inversedBy="students")
	 * @JoinTable(name="students_class")
	 */
	protected $class;

	/**
	 * @OneToOne(targetEntity="\Model\Parents", inversedBy="student")
	 */
	protected $parents;

	public function addClass(\Model\Clas $class = null) {
		$this->class->add($class);
	}

	public function removeClass(\Model\Clas $class) {
		$this->class->removeElement($class);
	}

	public function addGroup(\Model\Group $group = null) {
		$this->groups->add($group);
	}

	public function removeGroup(\Model\Group $group) {
		$this->groups->removeElement($group);
	}

	public function removeAllGroups() {
		foreach ($this->groups as $s)
			$this->groups->removeElement($s);
	}

	public function __construct() {
		$this->class = new Collection;
		$this->groups = new Collection;
	}

}
