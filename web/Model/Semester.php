<?php

namespace Model;

use Doctrine\Common\Collections\ArrayCollection as Collection;

/**
 * @Entity
 * @HasLifecycleCallbacks()
 */
class Semester {

    use \AutoProperty;

    /**
     * @Column(type="integer")
     * @Id
     * @GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @Column(type="integer", nullable=false)
     */
    protected $semester;

    /**
     * @Column(type="date", nullable=false)
     */
    protected $fromDate;

    /**
     * @Column(type="date", nullable=false)
     */
    protected $toDate;

    /**
     * @ManyToOne(targetEntity="\Model\Year", inversedBy="semesters")
     * @JoinColumn(nullable=false)
     */
    protected $year;

    /** @OneToMany(targetEntity="\Model\RatingDesc", mappedBy="semester") */
    protected $ratingDescs;

}
