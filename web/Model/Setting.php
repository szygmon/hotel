<?php

namespace Model;

/**
 * @Entity()
 * @Table(name="settings")
 */
class Setting {

	use \AutoProperty;

	/** 
         * @Column(type="string", unique=true)
         * @Id 
         */
	protected $name;

	/** @Column(type="text") */
	protected $value;

}
