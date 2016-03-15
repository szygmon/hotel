<?php

namespace Model;

use Doctrine\Common\Collections\ArrayCollection as Collection;
use Model\User;

/**
 * @Entity
 * @HasLifecycleCallbacks()
 */
class Parents extends User {

	/**
     * @OneToOne(targetEntity="\Model\Student", mappedBy="father")
     */
    protected $student;

}
