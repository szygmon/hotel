<?php

namespace Model;

use Doctrine\Common\Collections\ArrayCollection as Collection;

/**
 * @Entity
 * @HasLifecycleCallbacks()
 */
class Notification {

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
    protected $msg;

    /**
     * @ManyToOne(targetEntity="\Model\User", inversedBy="notifications")
     * @JoinColumn(nullable=false)
     */
    protected $user;
    
}
