<?php

namespace Model;

use Doctrine\Common\Collections\ArrayCollection as Collection;

/**
 * @Entity
 * @HasLifecycleCallbacks()
 */
class Classroom {

    use \AutoProperty;

    /**
     * @Column(type="integer")
     * @Id
     * @GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @Column(type="string", length=255, nullable=false)
     */
    protected $name;

    /**
     * @Column(type="integer", nullable=true)
     */
    protected $seats;

    /** @Column(type="boolean") */
    protected $projector = false;

    /**
     * @Column(type="string", length=255, nullable=true)
     */
    protected $others;

    /** @OneToMany(targetEntity="\Model\Plan", mappedBy="classroom") */
    protected $plans;

    public function addPlan(\Model\Plan $plan = null) {
        $this->plans->add($plan);
    }

    public function removePlan(\Model\Plan $plan) {
        $this->plans->removeElement($plan);
    }

    public function __construct() {
        $this->plans = new \Doctrine\Common\Collections\ArrayCollection();
    }

}
