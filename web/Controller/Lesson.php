<?php

namespace Controller;

use Model\Object;
use Core\Response;

class Lesson {

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
     * @Route(/teacher/lesson)
     */
    public function index() {

        return array('classes' => '$classes');
    }

    /**
     * @Route(/teacher/lessons/edit/{id})
     * @param \Core\Router $Router
     */
    public function editLesson($Router, $id = '') {
        if (isset($_POST['save'])) { // lekcja
            if (is_numeric($id)) { // edycja
                $lesson = $this->em->getRepository('Model\\Lesson')->find($id);
            } else { // nowy
                $lesson = new \Model\Lesson();
                $lesson->setTopic($_POST['topic']);
                $teacher = $this->em->getRepository('\Model\\Teacher')->find($_POST['teacher']);
                $lesson->setTeacher($teacher);
                $class = $this->em->getRepository('\Model\\Clas')->find($_POST['class']);
                $lesson->setClass($class);
                $lesson->setDate(new \DateTime($_POST['date']));
                $h = $this->em->getRepository('\Model\\Hour')->find($_POST['hour']);
                $lesson->setHour($h);
                $subject = $this->em->getRepository('\Model\\Subject')->find($_POST['subject']);
                $lesson->setSubject($subject);
                if ($_POST['group'] != 0) {
                    $g = $this->em->getRepository('\Model\\group')->find($_POST['group']);
                    $lesson->setGroup($g);
                }

                $this->em->persist($lesson);
                $this->em->flush();
                $Router->redirect('Lesson/editLesson', array('id' => $lesson->getId()));
            }
        }

        // dodawanie opisu oceny
        if (isset($_POST['saveRatingDesc'])) {
            $class = $this->em->getRepository('\Model\\Clas')->find($_POST['class']);
            $subject = $this->em->getRepository('\Model\\Subject')->find($_POST['subject']);
            $orderDesc = $_POST['orderDesc'];

            // sprawdzenie czy jest takie
            $rd = $this->em->getRepository('\Model\\RatingDesc')->findBy(array(
                'class' => $class,
                'subject' => $subject,
                'orderDesc' => $orderDesc,
                'semester' => $this->me->getActualSemester() ///////////////////////////////////////////narazie tylko aktualny semestr
            ));

            if ($rd) { // edycja
                $rd[0]->setDescription($_POST['desc']);
                $rd[0]->setShortDesc($_POST['shortDesc']);
                $rd[0]->setWeight(is_numeric($_POST['weight']) ? $_POST['weight'] : 1);
                $rd[0]->setColor($_POST['color']);
                $this->em->flush();
                //$Router->redirect('Lesson/editLesson', array('id' => $_POST['lesson']));
            } else { // dodanie nowego
                $rd = new \Model\RatingDesc();

                $rd->setDescription($_POST['desc']);
                $rd->setShortDesc($_POST['shortDesc']);
                $rd->setWeight(is_numeric($_POST['weight']) ? $_POST['weight'] : 1);
                $rd->setClass($class);
                $rd->setSubject($subject);
                $rd->setOrderDesc($orderDesc);
                $rd->setColor($_POST['color']);
                $rd->setSemester($this->me->getActualSemester()); /////////////////////////////// tylko aktualny semestr

                $this->em->persist($rd);
                $this->em->flush();
                //$Router->redirect('Lesson/editLesson', array('id' => $_POST['lesson']));
            }
        }

        if (isset($_POST['saveRatings'])) { // zapisywanie ocen
            if (!is_numeric($id))
                die('ERROR!!!');

            $lesson = $this->em->getRepository('\Model\\Lesson')->find($id);
            $students = $lesson->getClass()->getStudents();
            for ($i = 1; $i < 21; $i++) {
                $rdesc = $this->em->getRepository('\Model\\RatingDesc')->findBy(array(
                    'class' => $lesson->getClass(),
                    'subject' => $lesson->getSubject(),
                    'orderDesc' => $i
                ));
                if ($rdesc) {
                    foreach ($students as $student) {
                        $find = false;
                        foreach ($rdesc[0]->getRatings() as $rating) {
                            if ($rating->getStudent()->getId() == $student->getId()) { // update
                                $find = true;
                                if ($_POST['rat' . $student->getId() . '-' . $i]) {
                                    if ($_POST['rat' . $student->getId() . '-' . $i] != $rating->getValue()) {
                                        $rating->setValue($_POST['rat' . $student->getId() . '-' . $i]);
                                        $rating->setDate(new \DateTime());
                                    }
                                } else { // usuwanie oceny która została usunięta z dziennika
                                    $this->em->remove($rating);
                                }
                            }
                        }
                        if (!$find) {
                            if ($_POST['rat' . $student->getId() . '-' . $i]) {
                                $rating = new \Model\Rating();
                                $rating->setStudent($student);
                                $rating->setSubject($lesson->getSubject());
                                $rating->setRatingDesc($rdesc[0]);
                                $rating->setClass($lesson->getClass());
                                $rating->setValue($_POST['rat' . $student->getId() . '-' . $i]);
                                $rating->setDate(new \DateTime());

                                $notif = new \Model\Notification(); // powiadomienie
                                $notif->setMsg('Nowa ocena ' . $_POST['rat' . $student->getId() . '-' . $i] . ' z ' . $lesson->getSubject()->getSubject());
                                $notif->setUser($student);
                                $this->em->persist($notif);

                                $this->em->persist($rating);
                            }
                        }
                    }
                }
            }
            $this->em->flush();
            $Router->redirect('Lesson/editLesson', array('id' => $id));
        }

        // frekwencja
        if (isset($_POST['saveAttendance'])) {
            $class = $this->em->getRepository('\Model\\Clas')->find($_POST['class']);
            $lesson = $this->em->getRepository('\Model\\Lesson')->find($id);

            foreach ($class->getStudents() as $student) {
                if ($_POST['attendance' . $student->getId()]) {
                    $a = $this->em->getRepository('\Model\\Attendance')->findOneBy(array('student' => $student, 'lesson' => $lesson));
                    if (!$a) {
                        $a = new \Model\Attendance();
                        $a->setStudent($student);
                        $a->setLesson($lesson);
                        $this->em->persist($a);
                    }
                    $a->setPresence($_POST['attendance' . $student->getId()]);
                }
            }
            $this->em->flush();
            $Router->redirect('Lesson/editLesson', array('id' => $id));
        }

        if (is_numeric($id)) { // edycja lekcji
            $lesson = $this->em->getRepository('Model\\Lesson')->find($id);

            $ratingDescs = $this->em->getRepository('\Model\\RatingDesc')->findBy(array(
                'class' => $lesson->getClass(),
                'subject' => $lesson->getSubject(),
                'semester' => $this->me->getActualSemester() //////////////////////////////// tylko oceny z aktualnego semestru
                    ), array('orderDesc' => 'ASC'));
        } else {
            $lesson = null;
            $ratings = null;
            $ratingd = null;
        }

        $groups = null;
        $this->me->groupList($groups);

        $hours = $this->em->getRepository('\Model\\Hour')->findAll();

        return array('lesson' => $lesson, 'hours' => $hours, 'groups' => $groups, 'get' => $_GET);
    }

    /**
     * @Route(/teacher/lessons/mylessons/{startDate})
     */
    public function myLessons($startDate = '') {

        for ($i = 0; $i < 5; $i++) {
            if ($startDate == '') {
                $date[$i + 1] = date("Y-m-d", strtotime('monday this week + ' . $i . ' days'));
            } else {
                $date[$i + 1] = date("Y-m-d", strtotime($startDate . ' + ' . $i . ' days'));
            }
            $data[$i + 1] = $this->me->getTeacherPlan($date[$i + 1]);
        }
        $startDate = $date[1];
        $endDate = date("Y-m-d", strtotime($startDate . ' + 4 days'));

        $hours = $this->em->getRepository('\Model\\Hour')->findAll();
        $dayname = array('godzina', 'poniedziałek', 'wtorek', 'środa', 'czwartek', 'piątek');

        return array('startDate' => $startDate, 'endDate' => $endDate, 'lessons' => $data, 'hours' => $hours, 'dayname' => $dayname, 'date' => $date);
    }

    /**
     * @Route(/teacher/lessons/attendance/{id})
     */
    public function attendance($id = '') {
        if (is_numeric($id)) {
            $lesson = $this->em->getRepository('Model\\Lesson')->find($id);

            $at = null;
            if ($lesson) {
                $lp = $this->em->getRepository('\Model\\Lesson')->findBy(array('class' => $lesson->getClass(), 'date' => $lesson->getDate()));
                foreach ($lp as $l) {
                    foreach ($l->getAttendances() as $a) {
                        $at[$a->getStudent()->getId()][$l->getHour()->getId()] = $a->getPresence();
                    }
                }
            }
        } else {
            $lesson = null;
            $at = null;
        }

        return array('lesson' => $lesson, 'attendance' => $at);
    }

    /**
     * @Route(/teacher/lessons/ratings/{id})
     */
    public function ratings($id = '') {
        if (is_numeric($id)) {
            $lesson = $this->em->getRepository('Model\\Lesson')->find($id);

            $ratingDescs = $this->em->getRepository('\Model\\RatingDesc')->findBy(array(
                'class' => $lesson->getClass(),
                'subject' => $lesson->getSubject(),
                'semester' => $this->me->getActualSemester() //////////////////////////////// tylko oceny z aktualnego semestru
                    ), array('orderDesc' => 'ASC'));
            foreach ($ratingDescs as $rd) {
                $ratingd[$rd->getOrderDesc()] = $rd;
                if ($rd->getRatings()) {
                    foreach ($rd->getRatings() as $r) {
                        $ratings[$r->getStudent()->getId()][$rd->getOrderDesc()] = $r;
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
                            if (isset($ratingsSum[$r->getStudent()->getId()])) {
                                $ratingsSum[$r->getStudent()->getId()] += $val * $rd->getWeight();
                                $counter[$r->getStudent()->getId()] += $rd->getWeight();
                            } else {
                                $ratingsSum[$r->getStudent()->getId()] = $val * $rd->getWeight();
                                $counter[$r->getStudent()->getId()] = $rd->getWeight();
                            }
                        }
                    }
                }
            }
            if (isset($ratingsSum)) {
                foreach ($ratingsSum as $key => $value) { // liczenie średniej
                    $ratingsAv[$key] = round($value / $counter[$key], 2);
                }
            }
        } else {
            $lesson = null;
            $ratings = null;
            $ratingd = null;
        }

        return array('lesson' => $lesson, 'ratingd' => $ratingd, 'ratings' => $ratings, 'ratingsAv' => $ratingsAv);
    }

}
