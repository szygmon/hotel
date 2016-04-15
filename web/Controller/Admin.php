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
     * @param \User\Me $Me
     * @param \Core\Router $Router
     */
    public function index($Me, $Router) {
        if (!$Me->auth('admin') && !$Me->auth('receptionist'))
            $Router->redirect('Admin/admin');

        $users = $this->em->createQueryBuilder()->select('COUNT(u.id)')->from('\Model\User', 'u')->getQuery()->getSingleScalarResult();
        $reservationsConfirm = $this->em->createQueryBuilder()->select('COUNT(r.id)')->from('\Model\Reservation', 'r')->where('r.paid = 1')->getQuery()->getSingleScalarResult();
        $reservationsNotConfirm = $this->em->createQueryBuilder()->select('COUNT(r.id)')->from('\Model\Reservation', 'r')->where('r.paid = 0')->getQuery()->getSingleScalarResult();
        $rooms = $this->em->createQueryBuilder()->select('COUNT(r.id)')->from('\Model\Room', 'r')->getQuery()->getSingleScalarResult();
        return array('users' => $users, 'reservationsConfirm' => $reservationsConfirm, 'reservationsNotConfirm' => $reservationsNotConfirm, 'rooms' => $rooms);
    }

    /**
     * Rooms
     * @Route(/admin/rooms/{action}/{id})
     * @param \User\Me $Me
     * @param \Core\Router $Router
     */
    public function rooms($Me, $Router, $action = null, $id = null) {
        if (!$Me->auth('admin'))
            $Router->redirect('Admin/admin');

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
     * @param \User\Me $Me
     * @param \Core\Router $Router
     */
    public function editRoom($Me, $Router, $id = null) {
        if (!$Me->auth('admin'))
            $Router->redirect('Admin/admin');

        if (is_numeric($id)) {
            $room = $this->em->getRepository('\Model\Room')->find($id);
        } else
            $room = null;

        return array('room' => $room);
    }

    /**
     * Rules
     * @Route(/admin/rules/{action})
     * @param \User\Me $Me
     * @param \Core\Router $Router
     */
    public function rules($Me, $Router, $action = null) {
        if (!$Me->auth('admin'))
            $Router->redirect('Admin/admin');

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
     * @param \User\Me $Me
     * @param \Core\Router $Router
     */
    public function tpay($Me, $Router, $action = null) {
        if (!$Me->auth('admin'))
            $Router->redirect('Admin/admin');

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
     * @param \User\Me $Me
     * @param \Core\Router $Router
     */
    public function reservations($Me, $Router, $action = null, $id = null) {
        if (!$Me->auth('admin') && !$Me->auth('receptionist'))
            $Router->redirect('Admin/admin');

        if ($action == 'del' && is_numeric($id)) {
            $res = $this->em->getRepository('\Model\Reservation')->find($id);
            $this->em->remove($res);
            $this->em->flush();
            \Notify::success('Usunięto rezerwację');
        }

        if ($action == 'noPaid' && is_numeric($id)) {
            $res = $this->em->getRepository('\Model\Reservation')->find($id);
            $res->setPaid(0);
            $this->em->flush();

            \Notify::success('Zaktualizowano.');
        } else if ($action == 'paidConfirm' && is_numeric($id)) {
            $res = $this->em->getRepository('\Model\Reservation')->find($id);
            $res->setPaid(1);
            $this->em->flush();

            \Notify::success('Zaktualizowano.');
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

    /**
     * Add rooms
     * @Route(/admin/editreservation/{id})
     * @param \User\Me $Me
     * @param \Core\Router $Router
     */
    public function editReservation($Me, $Router, $id = null) {
        if (!$Me->auth('admin'))
            $Router->redirect('Admin/admin');

        if (is_numeric($id)) {
            $reservation = $this->em->getRepository('\Model\Reservation')->find($id);
        } else
            $reservation = null;
        
        $users = $this->em->getRepository('\Model\User')->findAll();

        return array('reservation' => $reservation, 'users' => $users);
    }

    /**
     * Użytkownicy
     * @Route(/admin/users/{action}/{id})
     * @param \User\Me $Me
     * @param \Core\Router $Router
     */
    public function users($Me, $Router, $action = null, $id = null) {
        if (!$Me->auth('admin') && !$Me->auth('receptionist'))
            $Router->redirect('Admin/admin');

        if ($action == 'add') {
            $user = new \Model\User();
            $user->setUsername($_POST['username']);
            $user->setGivenName($_POST['givenName']);
            $user->setFamilyName($_POST['familyName']);
            if ($_POST['password'] == '')
                $user->setPassword('qwerty');
            else
                $user->setPassword($_POST['password']);
            $user->setEmail($_POST['email']);
            $user->setPhone($_POST['phone']);

            $this->em->persist($user);
            $this->em->flush();

            \Notify::success('Dodano użytkownika.');
        } else if ($action == 'updt' && is_numeric($id)) {
            $user = $this->em->getRepository('\Model\User')->find($id);
            $user->setUsername($_POST['username']);
            $user->setGivenName($_POST['givenName']);
            $user->setFamilyName($_POST['familyName']);
            if ($_POST['password'] != '')
                $user->setPassword($_POST['password']);
            $user->setEmail($_POST['email']);
            $user->setPhone($_POST['phone']);

            $this->em->flush();

            \Notify::success('Zaktualizowano dane użytkownika.');
        }

        $users = $this->em->getRepository('\Model\User')->findAll();

        return array('users' => $users);
    }

    /**
     * Użytkownicy - edycja
     * @Route(/admin/edituser/{id})
     * @param \User\Me $Me
     * @param \Core\Router $Router
     */
    public function editUser($Me, $Router, $id = null) {
        if (!$Me->auth('admin') && !$Me->auth('receptionist'))
            $Router->redirect('Admin/admin');
        $user = null;
        if ($id) {
            $user = $this->em->getRepository('\Model\User')->find($id);
        }

        return array('user' => $user);
    }

}
