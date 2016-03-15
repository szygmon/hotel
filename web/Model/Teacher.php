<?php

namespace Model;

use Doctrine\Common\Collections\ArrayCollection as Collection;

/**
 * @Entity
 */
class Teacher extends \Model\User {

    use \AutoProperty;
    /**
     * @Column(type="integer", nullable=true) 
     */
    protected $stopien; // przykład pola jakiegoś, potem się coś doda

    /** @OneToMany(targetEntity="\Model\Plan", mappedBy="teacher") */
    protected $plans;

    /**
     * @ManyToMany(targetEntity="\Model\Clas", mappedBy="educators")
     * @JoinTable(name="educators")
     */
    protected $classes;

    /** @OneToMany(targetEntity="\Model\Lesson", mappedBy="teacher") */
    protected $lessons;

}
