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
        $users = $this->em->createQueryBuilder()->select('COUNT(u.id)')->from('\Model\User', 'u')->getQuery()->getSingleScalarResult();
        $reservationsConfirm = $this->em->createQueryBuilder()->select('COUNT(r.id)')->from('\Model\Reservation', 'r')->where('r.paid = 1')->getQuery()->getSingleScalarResult();
        $reservationsNotConfirm = $this->em->createQueryBuilder()->select('COUNT(r.id)')->from('\Model\Reservation', 'r')->where('r.paid = 0')->getQuery()->getSingleScalarResult();
        $rooms = $this->em->createQueryBuilder()->select('COUNT(r.id)')->from('\Model\Room', 'r')->getQuery()->getSingleScalarResult();
        return array('users' => $users, 'reservationsConfirm' => $reservationsConfirm, 'reservationsNotConfirm' => $reservationsNotConfirm, 'rooms' => $rooms);
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
            $room->setCost($_POST['cost']);
            $room->setBalcony($_POST['balcony']);
            $room->setToilet($_POST['toilet']);
            $this->em->persist($room);
            $this->em->flush();

            \Notify::success('Dodano pokój do bazy danych.');
        } else if ($action == 'updt' && isset($_POST) && is_numeric($id)) {
            $room = $this->em->getRepository('\Model\Room')->find($id);
            $room->setNumber($_POST['number']);
            $room->setName($_POST['name']);
            $room->setUsers($_POST['users']);
            $room->setDescription($_POST['description']);
            $room->setCost($_POST['cost']);
            $room->setBalcony($_POST['balcony']);
            $room->setToilet($_POST['toilet']);
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

    public function settings($name = null) {
        if ($name != null) {
            $settings = $this->em->getRepository('\Model\Setting')->findOneBy(array('name' => $name));
            $return = $settings->value();
        } else {
            $return = $this->em->getRepository('\Model\Setting')->findAll();
        }

        return $return;
    }

    /**
     * tpay
     * @Route(/admin/tpay/{action})
     */
    public function tpay($action = null) {
        if ($action == 'save') {
            $tid = $this->em->getRepository('\Model\Setting')->find('tid');
            $tid->setValue($_POST['tid']);
            $tkey = $this->em->getRepository('\Model\Setting')->find('tkey');
            $tkey->setValue($_POST['tkey']);
            $this->em->flush();

            \Notify::success('Zapisano ustawienia tpay.com.');
        }

        return array('tid' => $this->settings('tid'), 'tkey' => $this->settings('tkey'));
    }

    /**
     * Rezerwacje
     * @Route(/admin/reservations/{action}/{id})
     */
    public function reservations($action = null, $id = null) {
        if ($action == 'del' && is_numeric($id)) {
            $res = $this->em->getRepository('\Model\Reservation')->find($id);
            $this->em->remove($res);
            $this->em->flush();
            \Notify::success('Usunięto rezerwację');
        }
        if ($action == 'old') {
            $reservations = $this->em->createQueryBuilder()
                    ->select('r.id, r.fromDate, r.toDate, r.reservationDate, r.paid, r.guest, u.givenName, u.familyName, u.id as uid')
                    ->from('\Model\Reservation', 'r')
                    ->leftJoin('r.user', 'u')
                    ->where('r.fromDate < ?1')
                    ->setParameter(1, new \DateTime())
                    ->orderBy('r.fromDate', 'DESC')
                    ->getQuery()
                    ->getResult();
        } else {
            $reservations = $this->em->createQueryBuilder()
                    ->select('r.id, r.fromDate, r.toDate, r.reservationDate, r.paid, r.guest, u.givenName, u.familyName, u.id as uid')
                    ->from('\Model\Reservation', 'r')
                    ->leftJoin('r.user', 'u')
                    ->where('r.fromDate >= ?1')
                    ->setParameter(1, new \DateTime())
                    ->orderBy('r.fromDate')
                    ->getQuery()
                    ->getResult();
        }

        return array('reservations' => $reservations, 'action' => $action);
    }

}
