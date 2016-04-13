<?php

namespace Model;

use Doctrine\Common\Collections\ArrayCollection as Collection;

/**
 * @Entity()
 * @Table(name="reservations")
 */
class Reservation {

    use \AutoProperty;

    /**
     * @Column(type="integer")
     * @Id
     * @GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /** @Column(type="date") */
    protected $fromDate;

    /** @Column(type="date") */
    protected $toDate;

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /** @Column(type="string", nullable=true) */
    protected $guest;

    /** @ManyToMany(targetEntity="\Model\Room", inversedBy="reservations")) 
     * @JoinTable(name="reservations_rooms")
     */
    protected $rooms;

    public function addRoom($room) {
        $this->rooms->add($room);
        return $this;
    }

    public function __construct() {
        $this->rooms = new Collection();
    }

    /** @Column(type="boolean") */
    protected $paid = false;

}
