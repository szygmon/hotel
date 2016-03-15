<?php

namespace Model;

use Doctrine\Common\Collections\ArrayCollection as Collection;

/**
 * @Entity
 * @HasLifecycleCallbacks()
 */
class Year {

	use \AutoProperty;

	/**
	 * @Column(type="integer")
	 * @Id
	 * @GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @Column(type="integer", nullable=false)
	 */
	protected $fromYear;

	/**
	 * @Column(type="integer", nullable=false)
	 */
	protected $toYear;

	/** @OneToMany(targetEntity="\Model\Clas", mappedBy="year") */
	protected $classes;

	/** @OneToMany(targetEntity="\Model\Semester", mappedBy="year") */
	protected $semesters;

}
