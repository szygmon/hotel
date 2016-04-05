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
     * Rooms
     * @Route(/admin/rooms/{action}/{id})
     */
    public function rooms($action = null, $id = null) {
        if ($action == 'del' && is_numeric($id)) {
            $room = $this->em->getRepository('\Model\Room')->find($id);
            $this->em->remove($room);
            $this->em->flush();
            \Notify::success('Pokój został usunięty z bazy danych.');
        } else if ($action == 'add' && isset($_POST)) {
            $room = new \Model\Room();
            $room->setNumber($_POST['number']);
            $room->setName($_POST['name']);
            $room->setUsers($_POST['users']);
            $room->setDescription($_POST['description']);
            $this->em->persist($room);
            $this->em->flush();
            
            \Notify::success('Dodano pokój do bazy danych.');
        } else if ($action == 'updt' && isset($_POST) && is_numeric($id)) {
            $room = $this->em->getRepository('\Model\Room')->find($id);
            $room->setNumber($_POST['number']);
            $room->setName($_POST['name']);
            $room->setUsers($_POST['users']);
            $room->setDescription($_POST['description']);
            $this->em->flush();
            
            \Notify::success('Zaktualizowano pokój w bazie danych.');
        }
        
        $rooms = $this->em->getRepository('\Model\Room')->findAll();
        
        return array('rooms' => $rooms);
    }
    
    /**
     * Add rooms
     * @Route(/admin/editroom/{id})
     */
    public function editRoom($id = null) {
        if (is_numeric($id)) {
            $room = $this->em->getRepository('\Model\Room')->find($id);
        } else 
            $room = null;
            
        return array('room' => $room);
    }

    /**
     * Rules
     * @Route(/admin/rules/{action})
     */
    public function rules($action = null) {
        if ($action == 'save') {
            $rules = $this->em->getRepository('\Model\Setting')->find('rules');
            $rules->setValue($_POST['value']);
            $this->em->flush();
            
            \Notify::success('Zapisano regulamin.');
        }
        
        $rules = $this->em->getRepository('\Model\Setting')->find('rules');
        
        return array('rules' => $rules);
    }
}
