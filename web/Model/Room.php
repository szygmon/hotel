<?php

namespace Model;

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

	/** @Column(type="text") */
	protected $description;

        /** @Column(type="boolean") */
	protected $balcony;
        
        /** @Column(type="boolean") */
	protected $toilet;
}
