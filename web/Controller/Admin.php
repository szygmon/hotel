<?php

namespace Controller;

use Model\Object;
use Core\Response;

/**
 * organizacja szkoły
 * klasy, przedmioty, sale lekcyjne, godziny zajęć, plan lekcji
 */
class Admin {

    /** @var \User\Me */
    protected $me;

    /** @var \Doctrine\ORM\EntityManager */
    protected $em;

    /** @var \HomeUtil */
    protected $homeUtil;

    /**
     * @param \User\Me $Me
     * @param \Doctrine\ORM\EntityManager $em
     * @param \HomeUtil $HomeUtil
     */
    function __construct($Me, $em, $HomeUtil) {
        $this->me = $Me;
        $this->em = $em;
        $this->homeUtil = $HomeUtil;
    }

    /**
     * Login site
     * @Route(/admin)
     * @param \User\Me $Me
     * @param \Core\Router $Router
     */
    public function admin($Me, $Router) {
        if (isset($_POST['username']))
            $msg = $this->homeUtil->loginForm($_POST);

        if ($Me->auth('admin'))
            $Router->redirect('Admin/index');

        return array("salesPage" => !$Router->getSubdomain(), 'msg' => $msg);
    }

    /**
     * index
     * @Route(/admin/index)
     */
    public function index() {
        return array('classes' => '$classes');
    }

    
    /**
     * index
     * @Route(/admin/groups)
     */
    public function groups() {
        return array('classes' => '$classes');
    }

}
