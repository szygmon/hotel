<?php

namespace Controller;

use Core\Response;

class Home {

    /** @var \User\Me */
    protected $me;

    /** @var \Doctrine\ORM\EntityManager */
    protected $em;

    /** @var \HomeUtil */
    protected $homeUtil;

    /**
     * @param \Doctrine\ORM\EntityManager $em
     * @param \HomeUtil $HomeUtil
     */
    function __construct($em, $HomeUtil) {
        $this->em = $em;
        $this->homeUtil = $HomeUtil;
    }

    /**
     * Index Site
     * @Route()
     * @param \User\Me $Me
     * @param \Core\Router $Router
     */
    public function index($Me, $Router) {
        //(new \UserManager)->switchSchema('hotel');
        

        //if ($Me->auth('user'))
        //  return new Response([], 'Home/indexSchool');

        return array("salesPage" => !$Router->getSubdomain());
    }

    /**
     * Pokoje
     * @Route(/rooms)
     */
    public function rooms() {

        return array("salesPage" => true);
    }
/**
     * Pokoje
     * @Route(/rooms_oldcopy)
     */
    public function rooms_oldcopy() {

        return array("salesPage" => true);
    }
    
    /**
     * Pokoje
     * @param \User\Me $Me
     * @param \Core\Router $Router
     * @Route(/account)
     */
    public function account($Me, $Router) {
        if (!$Me->auth('user'))
            $Router->redirect('Home/index');
        
        if (isset($_POST['username'])) 
            $msg = $this->homeUtil->updateForm($_POST);
        return array("salesPage" => true);
    }

    /**
     * Pokoje
     * @param \User\Me $Me
     * @param \Core\Router $Router
     * @Route(/signin)
     */
    public function signin() {
        $msg = null;
        if (isset($_POST['username']))
            $msg = $this->homeUtil->loginForm($_POST);

        return array("salesPage" => true, 'msg' => $msg);
    }

    /**
     * Pokoje
     * @Route(/offert)
     */
    public function offert() {

        return array("salesPage" => true);
    }

    /**
     * Pokoje
     * @Route(/reviews)
     */
    public function reviews() {

        return array("salesPage" => true);
    }

    /**
     * Typografia - chwilowo
     * @Route(/tp)
     */
    public function tp() {

        return array("salesPage" => true);
    }

    /**
     * Pokoje
     * @Route(/details)
     */
    public function details() {

        return array("salesPage" => true);
    }

    /**
     * Pokoje
     * @Route(/contact)
     */
    public function contact() {

        return array("salesPage" => true);
    }

    /**
     * Pokoje
     * @Route(/discover)
     */
    public function discover() {

        return array("salesPage" => true);
    }

    /**
     * Register
     * @Route(/register)
     */
    public function register() {
        if (isset($_POST['username']))
            $msg = $this->homeUtil->registerForm($_POST);
        return array("salesPage" => true);
    }

    /**
     * @Route(/removeNotif)
     */
    public function removeNotif() {
        $n = $this->em->getRepository('\Model\\Notification')->find($_POST['id']);
        $this->em->remove($n);
        $this->em->flush();

        return array("info" => "brak");
    }


    /**
     * Logout
     * @Route("/logout")
     * @param \User\Me $Me
     * @param \Core\Router $Router
     */
    public function logout($Me, $Router) {
        $Me->logout();
        $Router->redirect('Home/index');
    }

}
