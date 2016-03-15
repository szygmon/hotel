<?php

namespace Model;

use Doctrine\Common\Collections\ArrayCollection as Collection;

/**
 * @Entity
 * @HasLifecycleCallbacks()
 */
class Subject {

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
    protected $subject;

    /** @OneToMany(targetEntity="\Model\Rating", mappedBy="subject") */
    protected $ratings;

    /** @OneToMany(targetEntity="\Model\Plan", mappedBy="subject") */
    protected $plans;

    /** @OneToMany(targetEntity="\Model\RatingDesc", mappedBy="subject") */
    protected $ratingDescs;

    /** @OneToMany(targetEntity="\Model\Lesson", mappedBy="subject") */
    protected $lessons;

    public function getSubject() {
        return $this->subject;
    }

    public function setSubject($sub) {
        $this->subject = $sub;
        return $this;
    }

}
