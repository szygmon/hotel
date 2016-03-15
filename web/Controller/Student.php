<?php

namespace Controller;

use Model\Object;
use Core\Response;

// organizacja szkoły
// klasy, przedmioty, sale lekcyjne, godziny zajęć, plan lekcji
class Student {

    /** @var \User\Me */
    protected $me;

    /** @var \Doctrine\ORM\EntityManager */
    protected $em;

    /**
     * @param \User\Me $Me
     * @param \Doctrine\ORM\EntityManager $em
     */
    function __construct($Me, $em) {
        $this->me = $Me;
        $this->em = $em;
    }

    /**
     * index
     * @Route(/student)
     */
    public function index() {
        $year = $this->me->getActualYear();
        foreach ($this->me->getModel()->getClass() as $c) {
            if ($c->getYear() == $year)
                $class = $c;
        }

        $groups = $this->me->getModel()->getGroups();
        if ($groups) {
            foreach ($groups as $g) {
                $gids[] = $g->getId();
            }
        } else {
            $gids = array();
        }
        $plan = $this->em->createQueryBuilder()
                ->select('p')
                ->from('\Model\\Plan', 'p')
                ->where('p.fromDate <= ?1 AND p.toDate >= ?1 AND p.class = ?2 AND (p.group is NULL OR p.group IN (?3)) AND (p.day = ?4 OR p.day = ?5) ')
                ->orderBy('p.day')
                ->orderBy('p.hour')
                ->setParameters(array(1 => new \DateTime(), 2 => $class, 3 => $gids, 4 => date('N'), 5 => (date('N') + 1)))
                ->getQuery()
                ->getResult();
        foreach ($plan as $p) {
            if ($p->getDay() >= date('N')) {
                $i = $p->getDay() - date('N');
                if (date('Y-m-d', strtotime(date('Y-m-d') . ' + ' . $i . ' days')) <= date('Y-m-d', $p->getToDate()->getTimestamp())) {
                    $s[$p->getDay() == date('N') ? 'today' : 'tomorrow'][$p->getHour()->getId()] = $p;
                }
            } else {
                $i = 7 - (date('N') - $p->getDay());
                if (date('Y-m-d', strtotime(date('Y-m-d') . ' + ' . $i . ' days')) <= date('Y-m-d', $p->getToDate()->getTimestamp())) {
                    $s[$p->getDay() == date('N') ? 'today' : 'tomorrow'][$p->getHour()->getId()] = $p;
                }
            }
        }

        $hours = $this->em->getRepository('\Model\\Hour')->findAll();

        return array('plan' => $s, 'hours' => $hours);
    }

    /**
     * Oceny
     * @Route(/student/ratings/{class})
     */
    public function ratings($class = '') {
        if ($class == '') { // aktualna klasa
            $year = $this->me->getActualYear();
            foreach ($this->me->getModel()->getClass() as $c) {
                if ($c->getYear() == $year)
                    $class = $c;
            }
        } // dodać wcześniejsze////////////////////////////////////////////////////

        foreach ($this->me->getModel()->getRatings() as $r) {
            if ($r->getClass() == $class) {
                if (!isset($subjects[$r->getSubject()->getId()])) {
                    $subjects[$r->getSubject()->getId()] = $r->getSubject();
                }
                $ratings[$r->getSubject()->getId()][$r->getRatingDesc()->getOrderDesc()] = $r;
                if (is_numeric(substr($r->getValue(), 0, 1))) {
                    if (strpos($r->getValue(), '/')) { // jeśli poprawiana ocena
                        $v = explode('/', $r->getValue());
                        for ($i = 0; $i < 2; $i++) {
                            if (substr($v[$i], 1, 1) == '+')
                                $v[$i] = substr($v[$i], 0, 1) + 0.25;
                            else if (substr($v[$i], 1, 1) == '-')
                                $v[$i] = substr($v[$i], 0, 1) - 0.25;
                        }
                        $val = ($v[0] + $v[1]) / 2;
                    } else { // sama ocena bez poprawy
                        $val = $r->getValue();
                        if (substr($val, 1, 1) == '+')
                            $val = substr($val, 0, 1) + 0.25;
                        else if (substr($val, 1, 1) == '-')
                            $val = substr($val, 0, 1) - 0.25;
                    }
                    if (isset($ratingsSum[$r->getSubject()->getId()])) {
                        $ratingsSum[$r->getSubject()->getId()] += $val * $r->getRatingDesc()->getWeight();
                        $counter[$r->getSubject()->getId()] += $r->getRatingDesc()->getWeight();
                    } else {
                        $ratingsSum[$r->getSubject()->getId()] = $val * $r->getRatingDesc()->getWeight();
                        $counter[$r->getSubject()->getId()] = $r->getRatingDesc()->getWeight();
                    }
                }
            }
        }
        if ($ratingsSum) {
            foreach ($ratingsSum as $key => $value) { // liczenie średniej
                $ratingsAv[$key] = round($value / $counter[$key], 2);
            }
        }

        return array('ratings' => $ratings, 'subjects' => $subjects, 'ratingsAv' => $ratingsAv, 'class' => $class);
    }

    /**
     * Plan lekcji
     * @Route(/student/plan)
     */
    public function plan() {
        $year = $this->me->getActualYear();
        foreach ($this->me->getModel()->getClass() as $c) {
            if ($c->getYear() == $year)
                $class = $c;
        }

        $groups = $this->me->getModel()->getGroups();
        if ($groups) {
            foreach ($groups as $g) {
                $gids[] = $g->getId();
            }
        } else {
            $gids = array();
        }
        $plan = $this->em->createQueryBuilder()
                ->select('p')
                ->from('\Model\\Plan', 'p')
                ->where('p.fromDate <= ?1 AND p.toDate >= ?1 AND p.class = ?2 AND (p.group is NULL OR p.group IN (?3))')
                ->orderBy('p.day')
                ->orderBy('p.hour')
                ->setParameters(array(1 => new \DateTime(), 2 => $class, 3 => $gids))
                ->getQuery()
                ->getResult();
        foreach ($plan as $p) {
            if ($p->getDay() >= date('N')) {
                $i = $p->getDay() - date('N');
                if (date('Y-m-d', strtotime(date('Y-m-d') . ' + ' . $i . ' days')) <= date('Y-m-d', $p->getToDate()->getTimestamp())) {
                    $s[$p->getHour()->getId()][$p->getDay()] = $p;
                }
            } else {
                $i = 7 - (date('N') - $p->getDay());
                if (date('Y-m-d', strtotime(date('Y-m-d') . ' + ' . $i . ' days')) <= date('Y-m-d', $p->getToDate()->getTimestamp())) {
                    $s[$p->getHour()->getId()][$p->getDay()] = $p;
                }
            }
        }
        $return = array(1 => $s[1], 2 => $s[2], 3 => $s[3], 4 => $s[4], 5 => $s[5], 6 => $s[6], 7 => $s[7], 8 => $s[8]);

        $dayname = array('godzina', 'poniedziałek', 'wtorek', 'środa', 'czwartek', 'piątek');

        $hours = $this->em->getRepository('\Model\\Hour')->findAll();

        return array('plan' => $return, 'class' => $class, 'dayname' => $dayname, 'hours' => $hours);
    }

    /**
     * Frekwencja
     * @Route(/student/attendance/{startDate})
     */
    public function attendance($startDate = '') {
        // szukanie aktualnej klasy
        $myClasses = $this->me->getModel()->getClass();
        foreach ($myClasses as $c) {
            $sem = $c->getYear()->getSemesters();
            foreach ($sem as $s) {
                if ($s == $this->me->getActualSemester())
                    $class = $c;
            }
        }

        for ($i = 0; $i < 5; $i++) {
            if ($startDate == '') {
                $date[$i + 1] = date("Y-m-d", strtotime('monday this week + ' . $i . ' days'));
            } else {
                $date[$i + 1] = date("Y-m-d", strtotime($startDate . ' + ' . $i . ' days'));
            }
            $lessons = $this->em->getRepository('\Model\\Lesson')->findBy(array('date' => new \DateTime($date[$i + 1]), 'class' => $class));
            foreach ($lessons as $l) {
                $lesson[$i + 1][$l->getHour()->getId()] = $l;
                $find = false;
                foreach ($l->getAttendances() as $a) {
                    if ($a->getStudent() == $this->me->getModel()) {
                        $find = true;
                        $attendance[$i + 1][$l->getHour()->getId()] = $a;
                    }
                }
                if (!$find)
                    $attendance[$i + 1][$l->getHour()->getId()] = 'B/D';
            }
        }
        $startDate = $date[1];
        $endDate = date("Y-m-d", strtotime($startDate . ' + 4 days'));

        $hours = $this->em->getRepository('\Model\\Hour')->findAll();
        $dayname = array('godzina', 'poniedziałek', 'wtorek', 'środa', 'czwartek', 'piątek');

        return array('startDate' => $startDate, 'endDate' => $endDate, 'lesson' => $lesson, 'attendance' => $attendance, 'hours' => $hours, 'dayname' => $dayname, 'date' => $date);
    }

}
