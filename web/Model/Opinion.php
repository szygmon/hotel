<?php

namespace Model;

use Doctrine\Common\Collections\ArrayCollection as Collection;

/**
 * @Entity()
 * @Table(name="opinions")
 */
class Opinion {

    use \AutoProperty;

    /**
     * @Column(type="integer")
     * @Id
     * @GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /** @Column(type="string") */
    protected $content;

    /** @Column(type="integer") */
    protected $rating;

    /** @Column(type="date") */
    protected $date;
    
    /** @Column(type="boolean") */
    protected $isVerified = false;
    
    /** @Column(type="boolean") */
    protected $isActive = false;
    
    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_opinion", referencedColumnName="id")
     */
    protected $user;
}
