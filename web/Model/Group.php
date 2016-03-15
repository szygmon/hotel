<?php

namespace Model;

use Doctrine\Common\Collections\ArrayCollection as Collection;

/**
 * @Entity
 * @Table(name="groups")
 */
class Group {

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
    protected $name;

    /**
     * @OneToMany(targetEntity="\Model\Group", mappedBy="mainGroup")
     */
    protected $subGroups;

    /**
     * @ManyToOne(targetEntity="\Model\Group", inversedBy="subGroups")
     */
    protected $mainGroup;

    /** @OneToMany(targetEntity="\Model\Plan", mappedBy="group") */
    protected $plans;
    
    /** @OneToMany(targetEntity="\Model\Lesson", mappedBy="group") */
    protected $lessons;

    /**
     * @ManyToMany(targetEntity="\Model\Student", mappedBy="groups")
     * @JoinTable(name="students_groups")
     */
    protected $students;

    public function addStudent(\Model\Student $student = null) {
        $this->students->add($student);
    }

    public function removeStudent(\Model\Student $student) {
        $this->students->removeElement($student);
    }

    public function __construct() {
        $this->students = new \Doctrine\Common\Collections\ArrayCollection();
    }

}
