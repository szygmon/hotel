<?php

namespace Model;

use Doctrine\Common\Collections\ArrayCollection as Collection;

/**
 * @Entity
 * @HasLifecycleCallbacks()
 */
class Plan {

	use \AutoProperty;

	/**
	 * @Column(type="integer")
	 * @Id
	 * @GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ManyToOne(targetEntity="\Model\Hour", inversedBy="plans")  
	 * @JoinColumn(nullable=false)      
	 */
	protected $hour;

	/**
	 * @Column(type="integer", nullable=false)
	 */
	protected $day;

	/**
	 * @ManyToOne(targetEntity="\Model\Subject", inversedBy="plans")  
	 * @JoinColumn(nullable=false)      
	 */
	protected $subject;

	/**
	 * @ManyToOne(targetEntity="\Model\Teacher", inversedBy="plans")  
	 * @JoinColumn(nullable=false)      
	 */
	protected $teacher;

	/**
	 * @ManyToOne(targetEntity="\Model\Classroom", inversedBy="plans")  
	 * @JoinColumn(nullable=true)      
	 */
	protected $classroom;

	/**
	 * @ManyToOne(targetEntity="\Model\Clas", inversedBy="plans")  
	 * @JoinColumn(nullable=false)      
	 */
	protected $class;

	/**
	 * @ManyToOne(targetEntity="\Model\Group", inversedBy="plans")  
	 * @JoinColumn(nullable=true)      
	 */
	protected $group;

	/**
	 * @Column(type="date", nullable=false)
	 */
	protected $fromDate;

	/**
	 * @Column(type="date", nullable=false) 
	 */
	protected $toDate;

}
