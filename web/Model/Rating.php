<?php

namespace Model;

use Doctrine\Common\Collections\ArrayCollection as Collection;

/**
 * @Entity
 * @HasLifecycleCallbacks()
 */
class Rating {

    use \AutoProperty;

    /**
     * @Column(type="integer")
     * @Id
     * @GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="\Model\Student", inversedBy="ratings")  
     * @JoinColumn(nullable=false)      
     */
    protected $student;

    /**
     * @ManyToOne(targetEntity="\Model\Subject", inversedBy="ratings")  
     * @JoinColumn(nullable=false)      
     */
    protected $subject;
    
     /**
     * @ManyToOne(targetEntity="\Model\RatingDesc", inversedBy="ratings")  
     * @JoinColumn(nullable=false)      
     */
    protected $ratingDesc;

    ///** 
    // * @Column(type="integer", nullable=false) 
    // */
    //protected $order;

    /**
     * @Column(type="string", length=20, nullable=false)
     */
    protected $value;

    /** @Column(type="datetime", nullable=false) */
    protected $date;

    /**
     * @ManyToOne(targetEntity="\Model\Clas", inversedBy="ratings")
     * @JoinColumn(nullable=false)
     */
    protected $class;

}
