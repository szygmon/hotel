<?php

namespace Model;

use Doctrine\Common\Collections\ArrayCollection as Collection;

/**
 * @Entity
 * @HasLifecycleCallbacks()
 */
class Hour {

	use \AutoProperty;

	/**
	 * @Column(type="integer")
	 * @Id
	 * @GeneratedValue(strategy="AUTO")
	 */
	protected $id;
        
        /**
	 * @Column(type="time", nullable=false)
	 */
	protected $fromTime;

	/** 
         * @Column(type="time", nullable=false) 
         */
	protected $toTime;

        /** @OneToMany(targetEntity="\Model\Plan", mappedBy="hour") */
        protected $plans;
        
        /** @OneToMany(targetEntity="\Model\Lesson", mappedBy="hour") */
        protected $lessons;
}
