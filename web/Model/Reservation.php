<?php

namespace Model;

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

    /** @Column(type="string") */
    protected $guest;

    /**
     * @OneToMany(targetEntity="Room", mappedBy="Reservation")
     */
    protected $rooms;
    
    public function __construct() {
        $this->rooms = new ArrayCollection();
    }

    /** @Column(type="boolean") */
    protected $paid = false;

}
