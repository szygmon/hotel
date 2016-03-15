<?php

namespace Model;

use Doctrine\Common\Collections\ArrayCollection as Collection;

/**
 * @Entity
 * @HasLifecycleCallbacks()
 */
class Clas {

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
     * @ManyToOne(targetEntity="\Model\Year", inversedBy="classes")
     * @JoinColumn(nullable=false)
     */
    protected $year;

    /** @OneToMany(targetEntity="\Model\Plan", mappedBy="class") */
    protected $plans;

    /** @OneToMany(targetEntity="\Model\Rating", mappedBy="class") */
    protected $ratings;
    ///** @OneToMany(targetEntity="\Model\Behavior", mappedBy="clas") */
    //protected $behaviors;
    /** @OneToMany(targetEntity="\Model\RatingDesc", mappedBy="class") */
    protected $ratingDescs;

    /**
     * @ManyToMany(targetEntity="\Model\Teacher", inversedBy="classes")
     * @JoinTable(name="educators")
     */
    protected $educators;

    /** @OneToMany(targetEntity="\Model\Lesson", mappedBy="class") */
    protected $lessons;

    /**
     * @ManyToMany(targetEntity="\Model\Student", mappedBy="class")
     * @JoinTable(name="students_class")
     */
    protected $students;

    public function addStudent(\Model\Student $student = null) {
        $this->students->add($student);
    }

    public function removeStudent(\Model\Clas $student) {
        $this->students->removeElement($student);
    }

    public function removeAllStudents() {
        foreach ($this->students as $s)
            $this->students->removeElement($s);
    }

    public function __construct() {
        $this->students = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

}
