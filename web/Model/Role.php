<?php

namespace Model;

/**
 * @Entity()
 * @Table(schema="public", name="roles")
 */
class Role {

	/**
	 * @Column(type="integer")
	 * @Id
	 * @GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/** @Column(type="string") */
	protected $name;

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @param string $name
	 * @return \Model\Role
	 */
	public function setName($name) {
		$this->name = $name;
		return $this;
	}

}
