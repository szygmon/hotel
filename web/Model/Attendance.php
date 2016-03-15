<?php

namespace Model;

use Doctrine\Common\Collections\ArrayCollection as Collection;

/**
 * @Entity
 * @HasLifecycleCallbacks()
 */
class Attendance {

	use \AutoProperty;

	/**
	 * @Column(type="integer")
	 * @Id
	 * @GeneratedValue(strategy="AUTO")
	 */
	protected $id;
        
        /**
	 * @ManyToOne(targetEntity="\Model\Student", inversedBy="attendances")
	 * @JoinColumn(nullable=false)
	 */
	protected $student;
        
        /**
        * @ManyToOne(targetEntity="\Model\Lesson", inversedBy="attendances")  
        * @JoinColumn(nullable=false)      
        */
        protected $lesson;
        
	/** 
         * 1 - obecny, 2 - nieobecny, ...
         * @Column(type="integer", nullable=false) 
         */
	protected $presence;

}
