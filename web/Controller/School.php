<?php

namespace Controller;

use Model\Object;
use Core\Response;

/**
 * organizacja szkoły
 * klasy, przedmioty, sale lekcyjne, godziny zajęć, plan lekcji
 */
class School {

    /** @var \User\Me */
    protected $me;

    /** @var \Doctrine\ORM\EntityManager */
    protected $em;
   
	/** @var string */
	protected $info = 'brak';

    /**
     * @param \User\Me $Me
     * @param \Doctrine\ORM\EntityManager $em
     */
    function __construct($Me, $em) {
        $this->me = $Me;
        $this->em = $em;
    }

    // info do przekazania 
    public function info($inf) {
        if ($inf == 'err') {
            $this->info = array('inf' => 'Taka pozycja już istnieje!', 'col' => 'red');
        } else if ($inf == 'err2') {
            $this->info = array('inf' => 'Nie można usunąć tego semestru!', 'col' => 'red');
        } else if ($inf == 'err3') {
            $this->info = array('inf' => 'Nie można usunąć grupy ponieważ zawiera podgrupy!', 'col' => 'red');
        } else if ($inf == 'added') {
            $this->info = array('inf' => 'Pozycja została dodana', 'col' => 'green');
        } else if ($inf == 'deleted') {
            $this->info = array('inf' => 'Pozycja została usunięta', 'col' => 'green');
        } else if ($inf == 'updt') {
            $this->info = array('inf' => 'Pozycja została zaktualizowana', 'col' => 'green');
        } else {
            $this->info = 'brak';
        }
        return $this->info;
    }

    /**
     * index
     * @Route(/admin/school)
     */
    public function index() {
        return array('classes' => '$classes');
    }

    /**
     * Klasy
     * @Route(/admin/school/classes/{action}/{id})
     * @param \Core\Router $Router
     */
    public function classes($Router, $id = '', $action = '') {
        // dodawanie
        if (isset($_POST['save']) || isset($_POST['saveAndAdd'])) {
            // sprawdzenie czy już takiej nie ma
            $y = $this->em->getRepository('Model\\Year')->find($_POST['year']);
            $qb = $this->em->getRepository('\Model\\Clas')->findOneBy(array('name' => $_POST['name'], 'year' => $y));
            if ($qb != NULL)
                \Notify::error('Taka klasa już istnieje!');
            else {
                $year = $this->em->getRepository('Model\\Year')->find($_POST['year']);

                $class = new \model\Clas();
                $class->setName($_POST['name']);
                $class->setYear($year);
                $this->em->persist($class);
                $this->em->flush();
                if (isset($_POST['saveAndAdd'])) {
                    \Notify::success('Dodano!');
                    $Router->redirect('School/classEdit', array('info' => 'added'));
                }
                \Notify::success('Dodano!');
            }
        }

        // ususwanie
        if ($action == 'del' && is_numeric($id) && $id > 0) {
            $class = $this->em->getRepository('\Model\\Clas')->find($id);
            if ($class->getStudents()[0]) {
                print('Nie można usunąć! Klasa zwiera studentów!');
                die;
            }
            if ($class->getRatings()[0]) {
                print('Nie można usunąć! Klasa zwiera przypisane oceny!');
                die;
            }
            if ($class->getLessons()[0]) {
                print('Nie można usunąć! Klasa zwiera odbyte lekcje!');
                die;
            }
            foreach ($class->getPlans() as $plan) {
                $this->em->remove($plan);
            }

            $this->em->remove($class);
            $this->em->flush();
            $this->info('deleted');
        }

        // lista
        $classes = $this->em->getRepository('Model\\Clas')->findBy(array(), array('year' => 'ASC', 'name' => 'ASC')); /// tutaj nie działa sortowanie bo year to nie rok tylko model
        // czyszczenie infa
        $info = $this->info;
        $this->info = 'brak';

        return array('classes' => $classes, 'info' => $info);
    }

    /**
     * @Route(/admin/school/classes/edit/{info})
     */
    public function classEdit($info = 'brak') {
        $inf = $this->info($info);

        $years = $this->em->getRepository('Model\\Year')->findAll();
        return array('years' => $years, 'info' => $inf);
    }

    /**
     * Grupy
     * @Route(/admin/school/groups/{action}/{id})
     * @param \Core\Router $Router
     */
    public function groups($Router, $id = '', $action = '') {
        // dodawanie
        if (isset($_POST['save']) || isset($_POST['saveAndAdd'])) {
            // aktualizacja
            if ($action == 'updt' && is_numeric($id)) {
                $group = $this->em->getRepository('\Model\\Group')->find($id);
                $group->setName($_POST['name']);
                if ($_POST['mainGroup'] != 0) {
                    $mainGroup = $this->em->getRepository('\Model\\Group')->find($_POST['mainGroup']);
                    $group->setMainGroup($mainGroup);
                } else
                    $group->setMainGroup(NULL);
                $this->em->flush();
                if (isset($_POST['saveAndAdd']))
                    $Router->redirect('School/groupEdit', array('info' => 'added'));
                $this->info('updt');
            } else { // nowa grupa
                $group = new \Model\Group();
                $group->setName($_POST['name']);
                if ($_POST['mainGroup'] != 0) {
                    $mainGroup = $this->em->getRepository('\Model\\Group')->find($_POST['mainGroup']);
                    $group->setMainGroup($mainGroup);
                }
                $this->em->persist($group);
                $this->em->flush();
                if (isset($_POST['saveAndAdd']))
                    $Router->redirect('School/groupEdit', array('info' => 'added'));
                $this->info('added');
            }
        }

        // ususwanie
        if ($action == 'del' && is_numeric($id) && $id > 0) {
            $group = $this->em->getRepository('\Model\\Group')->find($id);
            if ($this->em->getRepository('\Model\\Group')->findOneBy(array('mainGroup' => $group)) != NULL) {
                $this->info('err3');
            } else {
                ////////////////////////////////////////////////////////////////////////////notif potwierdzający: zostaną usunieci studenci z grup i plan zaiwerający przypis do grupy
                foreach ($group->getStudents() as $student) {
                    $group->removeStudent($student);
                }
                foreach ($group->getPlans() as $plan) {
                    $this->em->remove($plan);
                }

                $this->em->remove($group);
                $this->em->flush();
                $this->info('deleted');
            }
        }

        // czyszczenie infa
        $info = $this->info;
        $this->info = 'brak';

        $groups = null;
        $this->me->groupList($groups);
        return array('groups' => $groups, 'info' => $info);
    }

    /**
     * @Route(/admin/school/groups/edit/{id}/{info})
     */
    public function groupEdit($id = '', $info = 'brak') {
        if (is_numeric($id)) {
            $data = $this->em->getRepository('\Model\\Group')->find($id);
        } else {
            $data = null;
        }
        $inf = $this->info($info);
        $groups = null;
        $this->me->groupList($groups);
        return array('group' => $data, 'groups' => $groups, 'info' => $inf);
    }

    /**
     * Sale lekcyjne
     * @Route(/admin/school/classrooms/{action}/{id})
     * @param \Core\Router $Router
     */
    public function classrooms($Router, $id = '', $action = '') {
        if (isset($_POST['save']) || isset($_POST['saveAndAdd'])) { // dodawanie
            if ($action == 'updt' && is_numeric($id)) { // aktualizacja
                $classroom = $this->em->getRepository('\Model\\Classroom')->find($id);
                $classroom->setName($_POST['name']);
                $classroom->setSeats(!is_numeric($_POST['seats']) ? NULL : $_POST['seats']);
                $classroom->setProjector($_POST['projector']);
                $classroom->setOthers($_POST['others']);
                $this->em->flush();
                if (isset($_POST['saveAndAdd']))
                    $Router->redirect('School/classroomEdit', array('info' => 'added'));
                $this->info('updt');
            } else {
                $qb = $this->em->getRepository('\Model\\Classroom')->findOneBy(array('name' => $_POST['name']));
                if ($qb != NULL) // sprawdzenie czy już takiej nie ma
                    $this->info('err');
                else {
                    $classroom = new \model\Classroom();
                    $classroom->setName($_POST['name']);
                    $classroom->setSeats(!is_numeric($_POST['seats']) ? NULL : $_POST['seats']);
                    $classroom->setProjector($_POST['projector']);
                    $classroom->setOthers($_POST['others']);
                    $this->em->persist($classroom);
                    $this->em->flush();
                    if (isset($_POST['saveAndAdd']))
                        $Router->redirect('School/classroomEdit', array('info' => 'added'));
                    $this->info('added');
                }
            }
        }

        // ususwanie 
        if ($action == 'del' && is_numeric($id) && $id > 0) {
            $classroom = $this->em->getRepository('\Model\\Classroom')->find($id);
            foreach ($classroom->getPlans() as $p) {
                $p->setClassroom(null);
            }
            $this->em->remove($classroom);
            $this->em->flush();
            $this->info('deleted');
        }

        // lista
        $classrooms = $this->em->getRepository('Model\\Classroom')->findBy(array(), array('name' => 'ASC'));

        // czyszczenie infa
        $info = $this->info;
        $this->info = 'brak';

        return array('classrooms' => $classrooms, 'info' => $info);
    }

    /**
     * @Route(/admin/school/classrooms/edit/{id}/{info})
     */
    public function classroomEdit($id = '', $info = 'brak') {
        if (is_numeric($id)) {
            $classroom = $this->em->getRepository('\Model\\Classroom')->find($id);
        } else
            $classroom = null;

        $inf = $this->info($info);
        return array('info' => $inf, 'classroom' => $classroom);
    }

    /**
     * Godziny lekcyjne
     * @Route(/admin/school/hours/{action}/{id})
     */
    public function hours($id = '', $action = '') {
        // dodawanie
        if (isset($_POST['save'])) {
            $check = $this->em->getRepository('\Model\\Hour')->find(1);
            for ($i = 1; $i < 9; $i++) {
                if (!$check) {
                    $hour = new \model\Hour();
                } else {
                    $hour = $this->em->getRepository('\Model\\Hour')->find($i);
                }
                $hour->setFromTime(new \DateTime($_POST['hour' . $i . 'from']));
                $hour->setToTime(new \DateTime($_POST['hour' . $i . 'to']));
                if (!$check) {
                    $this->em->persist($hour);
                }
            }
            $this->info('updt');
            $this->em->flush();
        }

        // lista
        $hours = $this->em->getRepository('Model\\Hour')->findAll();

        // czyszczenie infa
        $info = $this->info;
        $this->info = 'brak';

        return array('hours' => $hours, 'info' => $info);
    }

    /**
     * @Route(/admin/school/hours/prepare)
     */
    public function hourPrepare() {
        return array('zmienna' => "brak");
    }

    /**
     * @Route(/admin/school/hours/edit/{id})
     */
    public function hourEdit($id = '') {
        if (is_numeric($id)) {
            $data = $this->em->getRepository('\Model\\Hour')->find($id);
        }

        return array('hour' => $data);
    }

    /**
     * Plan lekcji
     * @Route(/admin/school/plans/{action}/{id})
     */
    public function plans($id = '', $action = '') {
        // dodawanie
        if (isset($_POST['save'])) {
            if ($_POST['planid'] > 0) { //aktualizacja wpisu
                $plan = $this->em->getRepository('\Model\\Plan')->find($_POST['planid']);

                $date = explode(' - ', $_POST['dateRange']);
                $plan->setFromDate(new \DateTime($date[0]));
                $plan->setToDate(new \DateTime($date[1]));

                $h = $this->em->getRepository('\Model\\Hour')->find($_POST['hour']);
                $plan->setHour($h);
                $plan->setDay($_POST['day']);
                $s = $this->em->getRepository('\Model\\Subject')->find($_POST['subject']);
                $plan->setSubject($s);
                if ($_POST['classroom'] != 0) {
                    $c = $this->em->getRepository('\Model\\Classroom')->find($_POST['classroom']);
                    $plan->setClassroom($c);
                } else
                    $plan->setClassroom(null);
                if ($_POST['group'] != 0) {
                    $g = $this->em->getRepository('\Model\\Group')->find($_POST['group']);
                    $plan->setGroup($g);
                } else
                    $plan->setGroup(null);
                $t = $this->em->getRepository('\Model\\Teacher')->find($_POST['teacher']);
                $plan->setTeacher($t);
                $c = $this->em->getRepository('\Model\\Clas')->find($_POST['class']);
                $plan->setClass($c);

                $this->em->flush();
                $this->info('updt');
            } else { //nowy wpis
                $plan = new \Model\Plan();
                $date = explode(' - ', $_POST['dateRange']);
                $plan->setFromDate(new \DateTime($date[0]));
                $plan->setToDate(new \DateTime($date[1]));

                $h = $this->em->getRepository('\Model\\Hour')->find($_POST['hour']);
                $plan->setHour($h);
                $plan->setDay($_POST['day']);
                $s = $this->em->getRepository('\Model\\Subject')->find($_POST['subject']);
                $plan->setSubject($s);
                if ($_POST['classroom'] != 0) {
                    $c = $this->em->getRepository('\Model\\Classroom')->find($_POST['classroom']);
                    $plan->setClassroom($c);
                }
                if ($_POST['group'] != 0) {
                    $g = $this->em->getRepository('\Model\\Group')->find($_POST['group']);
                    $plan->setGroup($g);
                }

                $t = $this->em->getRepository('\Model\\Teacher')->find($_POST['teacher']);
                $plan->setTeacher($t);
                $c = $this->em->getRepository('\Model\\Clas')->find($_POST['class']);
                $plan->setClass($c);

                $this->em->persist($plan);
                $this->em->flush();
                $this->info('added');
            }
        }

        // usuwanie
        if ($action == 'del' && is_numeric($id) && $id > 0) {
            $plan = $this->em->getRepository('\Model\\Plan')->find($id);
            $this->em->remove($plan);
            $this->em->flush();
        }

        // lista
        $year = $this->me->getActualYear();
        $classes = $this->em->getRepository('Model\\Clas')->findBy(array('year' => $year), array('name' => 'ASC'));
        $subjects = $this->em->getRepository('Model\\Subject')->findBy(array(), array('subject' => 'ASC'));
        $classrooms = $this->em->getRepository('Model\\Classroom')->findBy(array(), array('name' => 'ASC'));
        $groups = null;
        $this->me->groupList($groups);
        $teachers = $this->em->getRepository('\Model\\Teacher')->findBy(array(), array('familyName' => 'ASC'));
        $hours = $this->em->getRepository('\Model\\Hour')->findAll();

        // czyszczenie infa
        $info = $this->info;
        $this->info = 'brak';

        return array('classes' => $classes, 'subjects' => $subjects, 'classrooms' => $classrooms, 'groups' => $groups, 'teachers' => $teachers, 'plan' => $this->getPlan(), 'hours' => $hours, 'info' => $info);
    }

    public function getPlan() {
        $year = $this->me->getActualYear();
        $classes = $this->em->getRepository('Model\\Clas')->findBy(array('year' => $year), array('name' => 'ASC'));
        foreach ($classes as $class) {
            $plan = $this->em->createQueryBuilder()
                    ->select('p')
                    ->from('\Model\\Plan', 'p')
                    ->where('p.fromDate <= ?1 AND p.toDate >= ?1 AND p.class = ?2')
                    ->orderBy('p.day')
                    ->orderBy('p.hour')
                    ->setParameters(array('1' => new \DateTime(), '2' => $class))
                    ->getQuery()
                    ->getResult();
            foreach ($plan as $p) {
                if ($p->getDay() >= date('N')) {
                    $i = $p->getDay() - date('N');
                    if (date('Y-m-d', strtotime(date('Y-m-d') . ' + ' . $i . ' days')) <= date('Y-m-d', $p->getToDate()->getTimestamp())) {
                        $s[$p->getHour()->getId()][$p->getDay()][] = $p;
                    }
                } else {
                    $i = 7 - (date('N') - $p->getDay());
                    if (date('Y-m-d', strtotime(date('Y-m-d') . ' + ' . $i . ' days')) <= date('Y-m-d', $p->getToDate()->getTimestamp())) {
                        $s[$p->getHour()->getId()][$p->getDay()][] = $p;
                    }
                }
            }
            $return[$class->getName()] = array(1 => $s[1], 2 => $s[2], 3 => $s[3], 4 => $s[4], 5 => $s[5], 6 => $s[6], 7 => $s[7], 8 => $s[8]);
            unset($s);
        }
        return $return;
    }

    /**
     * Przedmioty
     * @Route(/admin/school/subjects/{action}/{id})
     * @param \Core\Router $Router
     */
    public function subjects($Router, $id = '', $action = '') {
        // dodawanie
        if (isset($_POST['save']) || isset($_POST['saveAndAdd'])) {
            // sprawdzenie czy już takiego przedmiotu nie ma
            $qb = $this->em->getRepository('\Model\\Subject')->findOneBy(array('subject' => $_POST['name']));
            if ($qb != NULL)
                $this->info('err');
            else {
                $sub = new \Model\Subject();
                $sub->setSubject($_POST['name']);
                $this->em->persist($sub);
                $this->em->flush();
                if (isset($_POST['saveAndAdd']))
                    $Router->redirect('School/subEdit', array('info' => 'added'));
                $this->info('added');
            }
        }

        // ususwanie
        if ($action == 'del' && is_numeric($id) && $id > 0) {
            $subject = $this->em->getRepository('\Model\\Subject')->find($id);
            if ($subject->getRatings()[0]) {
                var_dump('Nie można usunąć! Do tego przedmiotu są przypisane oceny!');
                die;
            }
            if ($subject->getRatingDescs()[0]) {
                var_dump('Nie można usunąć! Do tego przedmiotu są przypisane opisy ocen!');
                die;
            }
            if ($subject->getLessons()[0]) {
                var_dump('Nie można usunąć! Do tego przedmiotu są przypisane tematy lekcji!');
                die;
            }
            foreach ($subject->getPlans() as $p) {
                $this->em->remove($p);
            }
            $this->em->remove($subject);
            $this->em->flush();
            $this->info('deleted');
        }

        // lista
        $subjects = $this->em->getRepository('Model\\Subject')->findBy(array(), array('subject' => 'ASC'));

        // czyszczenie infa
        $info = $this->info;
        $this->info = 'brak';

        return array('subjects' => $subjects, 'info' => $info);
    }

    /**
     * @Route(/admin/school/subjects/edit/{info})
     */
    public function subEdit($info = 'brak') {
        $inf = $this->info($info);
        return array('info' => $inf);
    }

    /**
     * Semestry
     * @Route(/admin/school/semesters/{action}/{id})
     */
    public function semesters($action = '', $id = '') {
        // dodawanie
        if (isset($_POST['save'])) {
            // aktualizacja
            if ($action == 'updt' && is_numeric($id)) {
                $sem = $this->em->getRepository('\Model\\Semester')->find($id);
                $semesters = $this->em->getRepository('\Model\\Semester')->findBy(array('year' => $sem->getYear()), array('semester' => 'ASC'));

                $date1 = explode(' - ', $_POST['sem1DateRange']);
                $semesters[0]->setFromDate(new \DateTime($date1[0]));
                $semesters[0]->setToDate(new \DateTime($date1[1]));

                $date2 = explode(' - ', $_POST['sem2DateRange']);
                $semesters[1]->setFromDate(new \DateTime($date2[0]));
                $semesters[1]->setToDate(new \DateTime($date2[1]));
                $this->em->flush();
				\Notify::success('Zaktualizowano semestr!');
            } else {
				// nowy
                $year = new \Model\Year();
                $year->setFromYear((int) $_POST['year']);
                $year->setToYear($_POST['year'] + 1);

                $sem1 = new \Model\Semester();
                $sem1->setSemester(1);
                $date = explode(' - ', $_POST['sem1DateRange']);
                $sem1->setFromDate(new \DateTime($date[0]));
                $sem1->setToDate(new \DateTime($date[1]));
                $sem1->setYear($year);

                $sem2 = new \Model\Semester();
                $sem2->setSemester(2);
                $date = explode(' - ', $_POST['sem2DateRange']);
                $sem2->setFromDate(new \DateTime($date[0]));
                $sem2->setToDate(new \DateTime($date[1]));
                $sem2->setYear($year);

                $this->em->persist($year);
                $this->em->persist($sem1);
                $this->em->persist($sem2);
                $this->em->flush();
				\Notify::success('Dodano semestr!');
            }
        }
        // usuwanie
        if ($action == 'del' && is_numeric($id) && $id > 0) {

            $sem = $this->em->getRepository('\Model\\Semester')->find($id);
            $date = new \DateTime('now');
            if ($date->format('Y') < $sem->getYear()->getFromYear()) { // sprawdzenie czy semestr nie jest aktualny albo się zaraz zacznie
                $rem = $this->em->getRepository('\Model\\Semester')->findBy(array('year' => $sem->getYear()));
                $this->em->remove($sem->getYear());
                foreach ($rem as $r) {
                    if ($r->getRatingDescs()[0]) {
                        print('Nie można usunąć! Do semestru są przypisane oceny!');
                        die;
                    }
                    $this->em->remove($r);
                }
                $this->em->flush();
                $this->info('deleted');
            } else {
                $this->info('err2');
            }
        }

        // lista
        $semesters = $this->em->getRepository('Model\\Semester')->findAll();

        // czyszczenie infa
        $info = $this->info;
        $this->info = 'brak';

        return array('semesters' => $semesters, 'info' => $info);
    }

    /**
     * @Route(/admin/school/semesters/edit/{id})
     */
    public function semestersEdit($id = '') {
        if (is_numeric($id)) {
            $semester = $this->em->getRepository('\Model\\Semester')->find($id);
            $semesters = $this->em->getRepository('\Model\\Semester')->findBy(array('year' => $semester->getYear()), array('semester' => 'ASC'));
        }
        return array('years' => $this->getYearsList(), 'semesters' => $semesters);
    }

// generator roczników od aktualnego do +6 bez tych co są w bazie
    public function getYearsList() {
        for ($i = date('Y') - 1; $i < date('Y') + 6; $i++) {
            $years[] = array('from' => $i, 'to' => ($i + 1), 'year' => $i . '/' . ($i + 1));
        }

        // wywalanie tych co już są
        $ys = $this->em->getRepository('Model\\Year')->findAll();
        foreach ($ys as $y) {
            $del_val = array('from' => $y->getFromYear(), 'to' => $y->getToYear(), 'year' => $y->getFromYear() . '/' . $y->getToYear());
            if (($key = array_search($del_val, $years)) !== false) {
                unset($years[$key]);
            }
        }

        return $years;
    }

    /**
     * Nauczyciele
     * @Route(/admin/school/teachers/{action}/{id})     
     * @param \Core\Router $Router
     */
    public function teachers($Router, $action = '', $id = '') {
        // dodawanie
        if (isset($_POST['save']) || isset($_POST['saveAndAdd'])) {
            // aktualizacja
            if ($action == 'updt' && is_numeric($id)) {
                $user = $this->em->getRepository('\Model\\Teacher')->find($id);
                $check = $this->em->getRepository('\Model\\User')->findBy(array('email' => $_POST['email']));
                if ($check[0] && $check[0]->getId() != $id) {
                    die('jest już taki mail!');
                }
                $user->setEmail($_POST['email']);
                $check = $this->em->getRepository('\Model\\User')->findBy(array('username' => $_POST['username']));
                if ($check[0] && $check[0]->getId() != $id) {
                    die('jest już taki username!');
                }
                $user->setUsername($_POST['username']);
                $user->setGivenName($_POST['givenName']);
                $user->setFamilyName($_POST['familyName']);
                if ($_POST['password'] != '')
                    $user->setPassword($_POST['password']);
                $this->em->flush();
                if (isset($_POST['saveAndAdd']))
                    $Router->redirect('School/teacherEdit', array('info' => 'updt'));
                $this->info('updt');
                // nowy
            } else {
                $user = new \Model\Teacher();
                $check = $this->em->getRepository('\Model\\User')->findBy(array('email' => $_POST['email']));
                if ($check) {
                    die('jest już taki mail!');
                }
                $user->setEmail($_POST['email']);
                $check = $this->em->getRepository('\Model\\User')->findBy(array('username' => $_POST['username']));
                if ($check) {
                    die('jest już taki username!');
                }
                $user->setUsername($_POST['username']);
                $user->setGivenName($_POST['givenName']);
                $user->setFamilyName($_POST['familyName']);
                if ($_POST['password'] == '')
                    $user->setPassword('qwerty');
                else
                    $user->setPassword($_POST['password']);
                $this->em->persist($user);
                $this->em->flush();
                if (isset($_POST['saveAndAdd']))
                    $Router->redirect('School/teacherEdit', array('info' => 'added'));
                $this->info('added');
            }
        }

        // usuwanie
        if ($action == 'del' && is_numeric($id) && $id > 0) {
            $t = $this->em->getRepository('\Model\\Teacher')->find($id); /// dodać jakieś waruki? żeby sie powiązania nie spieprzyły
            if ($t->getLessons()[0]) {
                print('Nie można usunąć! Ten nauczyciel prowadził lekcje! Zrobić nieaktywnego?');
                die;
            }
            foreach ($t->getPlans() as $p) {
                $this->em->remove($p);
            }
            $this->em->remove($t);
            $this->em->flush();
            $this->info('deleted');
        }

        // lista
        $teachers = $this->em->getRepository('Model\\Teacher')->findAll();

        // czyszczenie infa
        $info = $this->info;
        $this->info = 'brak';

        return array('teachers' => $teachers, 'info' => $info);
    }

    /**
     * @Route(/admin/school/teachers/edit/{info})
     */
    public function teacherEdit($info = 'brak') {
        if (!is_numeric($info)) {
            $inf = $this->info($info);
            $teacher = 'new';
        } else {
            $inf = 'brak';
            $teacher = $this->em->getRepository('Model\\Teacher')->find($info);
        }

        return array('info' => $inf, 'teacher' => $teacher);
    }

    /**
     * Uczniowie
     * @Route(/admin/school/students/{action}/{id})
     * @param \Core\Router $Router
     */
    public function students($Router, $action = '', $id = '') {
        // dodawanie
        if (isset($_POST['save']) || isset($_POST['saveAndAdd'])) {
            // aktualizacja
            if ($action == 'updt' && is_numeric($id)) {
                $user = $this->em->getRepository('\Model\\Student')->find($id);
                $check = $this->em->getRepository('\Model\\User')->findBy(array('email' => $_POST['email']));
                if ($check[0] && $check[0]->getId() != $id) {
                    die('jest już taki email!');
                }
                $user->setEmail($_POST['email']);
                $check = $this->em->getRepository('\Model\\User')->findBy(array('username' => $_POST['username']));
                if ($check[0] && $check[0]->getId() != $id) {
                    die('jest już taki username!');
                }
                $user->setUsername($_POST['username']);
                $user->setGivenName($_POST['givenName']);
                $user->setFamilyName($_POST['familyName']);
                if ($_POST['password'] != '')
                    $user->setPassword($_POST['password']);

                $user->setRegistrationNr($_POST['registrationNr']);
                $user->setPesel($_POST['pesel']);
                $user->setBirthdate(new \DateTime($_POST['birthdate']));
                $user->setBirthplace($_POST['birthplace']);

                $this->em->flush();
                if (isset($_POST['saveAndAdd']))
                    $Router->redirect('School/studentEdit', array('info' => 'updt'));
                $this->info('updt');
                // nowy
            } else {
                $user = new \Model\Student();
                $check = $this->em->getRepository('\Model\\User')->findBy(array('email' => $_POST['email']));
                if ($check) {
                    die('jest już taki email!');
                }
                $user->setEmail($_POST['email']);
                $check = $this->em->getRepository('\Model\\User')->findBy(array('username' => $_POST['username']));
                if ($check) {
                    die('jest już taki username!');
                }
                $user->setUsername($_POST['username']);
                $user->setGivenName($_POST['givenName']);
                $user->setFamilyName($_POST['familyName']);
                if ($_POST['password'] == '')
                    $user->setPassword('qwerty');
                else
                    $user->setPassword($_POST['password']);
                $user->setRegistrationNr($_POST['registrationNr']);
                $user->setPesel($_POST['pesel']);
                $user->setBirthdate(new \DateTime($_POST['birthdate']));
                $user->setBirthplace($_POST['birthplace']);

                if ($_POST['class'] != 0) {
                    $class = $this->em->getRepository('\Model\\Clas')->find($_POST['class']);
                    $user->addClass($class);
                }

                $this->em->persist($user);
                $this->em->flush();
                if (isset($_POST['saveAndAdd']))
                    $Router->redirect('School/studentEdit', array('info' => 'added'));
                $this->info('added');
            }
        }

        // usuwanie
        if ($action == 'del' && is_numeric($id) && $id > 0) {
            $t = $this->em->getRepository('\Model\\Student')->find($id); /// dodać jakieś waruki? żeby sie powiązania nie spieprzyły
            $this->em->remove($t);
            $this->em->flush();
            $this->info('deleted');
        }

        // lista
        $students = $this->em->getRepository('Model\\Student')->findAll();

        // czyszczenie infa
        $info = $this->info;
        $this->info = 'brak';

        return array('students' => $students, 'info' => $info);
    }

    /**
     * @Route(/admin/school/students/edit/{info})
     */
    public function studentEdit($info = 'brak') {
        if (!is_numeric($info)) {
            $inf = $this->info($info);
            $student = 'new';
        } else {
            $inf = 'brak';
            $student = $this->em->getRepository('Model\\Student')->find($info);
        }

        $years = $this->em->createQueryBuilder()
                ->select('y')
                ->from('\Model\\Year', 'y')
                ->where('y.toYear >= ?1')
                ->setParameter(1, date('Y'))
                ->getQuery()
                ->getResult();
        foreach ($years as $year) {
            $y[] = $year->getId();
        }

        $class = $this->em->createQueryBuilder('Model\\Clas')
                ->select('c')
                ->from('\Model\\Clas', 'c')
                ->where('c.year IN (:arr)')
                ->setParameter('arr', $y)
                ->orderBy('c.name')
                ->getQuery()
                ->getResult();

        return array('info' => $inf, 'student' => $student, 'class' => $class);
    }

}
