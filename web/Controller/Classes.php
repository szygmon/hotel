<?php

namespace Controller;

class Classes {

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
     * @Route(/teacher/class/{id})
     * @param \Core\Router $Router
     */
    public function classes($Router, $id = '') {
        if (is_numeric($id)) {
            $class = $this->em->getRepository('Model\\Clas')->find($id);
        }
        $groups = null;
        $this->me->groupList($groups);
        return array('class' => $class, 'groups' => $groups);
    }

    /**
     * @Route(/teacher/groups/{id})
     * @param \Core\Router $Router
     */
    public function studentsGroups($Router, $id = '') {
        $student = $this->em->getRepository('Model\\Student')->find($_POST['student']);
        $student->removeAllGroups();
        foreach ($_POST['groups'] as $group) {
            $g = $this->em->getRepository('\Model\\Group')->find($group);
            $student->addGroup($g);
        }
        $this->em->flush(); 
        $Router->redirect('Classes/classes', array('id' => $id));

        return array('class' => '');
    }

    /**
     * @Route(/teacher/class/students/{class}/{year})
     * @param \Core\Router $Router
     */
    public function students($Router, $year = '', $class = '') {
        if (isset($_POST['save'])) {
            $c = $this->em->getRepository('\Model\\Clas')->find($year);
            foreach ($_POST['students'] as $s) {
                $student = $this->em->getRepository('\Model\\Student')->find($s);
                $student->addClass($c);
            }
            $this->em->flush();
            $Router->redirect('Classes/classes', array('id' => $year));
        }

        if (isset($_POST['del'])) {
            $c = $this->em->getRepository('\Model\\Clas')->find($year);
            foreach ($_POST['students'] as $s) { // a co jak ma oceny już  wtej klasie???????
                $student = $this->em->getRepository('\Model\\Student')->find($s);
                $student->removeClass($c);
            }
            $this->em->flush();
            $Router->redirect('Classes/classes', array('id' => $year));
        }

        if (is_numeric($class)) {
            $class = $this->em->getRepository('\Model\\Clas')->find($class);
        }
        if (is_numeric($year)) {
            $start = new \DateTime($year . '-01-01');
            $end = new \DateTime($year . '-12-31');
            $students = $this->em->createQueryBuilder()
                    ->select('s')
                    ->from('\Model\\Student', 's')
                    ->where('s.birthdate >= ?1 AND s.birthdate <= ?2 ')
                    ->setParameters(array(1 => $start, 2 => $end))
                    ->getQuery()
                    ->getResult();
        }
        return array('class' => $class, 'students' => $students);
    }

}
