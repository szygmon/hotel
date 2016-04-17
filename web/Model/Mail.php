<?php

namespace Model;

use Doctrine\Common\Collections\ArrayCollection as Collection;

/**
 * @Entity()
 * @Table(name="mails")
 */
class Mail {

    use \AutoProperty;

    /**
     * @Column(type="integer")
     * @Id
     * @GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /** @Column(type="string") */
    protected $name;
    
    /** @Column(type="string") */
    protected $email;
    
    /** @Column(type="string") */
    protected $phone;
    
    /** @Column(type="string") */
    protected $content;

    /** @Column(type="boolean") */
    protected $isRead = false;
    
    /** @Column(type="boolean") */
    protected $isActive = true;
    
}
