<?php

namespace Model;

use Doctrine\Common\Collections\ArrayCollection as Collection;

/**
 * @Entity()
 * @Table(name="rooms")
 */
class Room {

    use \AutoProperty;

    /**
     * @Column(type="integer")
     * @Id
     * @GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /** @Column(type="string", unique=true) */
    protected $number;

    /** @Column(type="string") */
    protected $name;

    /** @Column(type="integer") */
    protected $users;
    
    /** @Column(type="float") */
    protected $cost;

    /** @Column(type="text") */
    protected $description;

    /** @Column(type="boolean") */
    protected $balcony;

    /** @Column(type="boolean") */
    protected $toilet;

    /** @ManyToMany(targetEntity="\Model\Reservation" , mappedBy="rooms")
     * @JoinTable(name="reservations_rooms") 
     */
    protected $reservations;

    public function __construct() {
        $this->reservations = new Collection();
    }

}
