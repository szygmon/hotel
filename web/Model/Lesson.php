<?php

namespace Model;

use Doctrine\Common\Collections\ArrayCollection as Collection;

/**
 * @Entity
 * @HasLifecycleCallbacks()
 */
class Lesson {

    use \AutoProperty;

    /**
     * @Column(type="integer")
     * @Id
     * @GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="\Model\Teacher", inversedBy="lessons")
     * @JoinColumn(nullable=false)
     */
    protected $teacher;

    /**
     * @ManyToOne(targetEntity="\Model\Clas", inversedBy="lessons")
     * @JoinColumn(nullable=false)
     */
    protected $class;

    /**
     * @Column(type="date", nullable=false)
     */
    protected $date;

    /**
     * @ManyToOne(targetEntity="\Model\Subject", inversedBy="lessons")
     * @JoinColumn(nullable=false)
     */
    protected $subject;

    /**
     * @ManyToOne(targetEntity="\Model\Hour", inversedBy="lessons")
     * @JoinColumn(nullable=false)
     */
    protected $hour;

    /**
     * @Column(type="string", length=255, nullable=false)
     */
    protected $topic;

    /** @OneToMany(targetEntity="\Model\Attendance", mappedBy="lesson") */
    protected $attendances;
    
    /**
     * @ManyToOne(targetEntity="\Model\Group", inversedBy="lessons")
     * @JoinColumn(nullable=true)
     */
    protected $group;

}
