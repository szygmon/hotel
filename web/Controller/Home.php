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
     * Księga gości
     * @Route(/reviews)
     */
    public function reviews() {
        if (isset($_POST['save'])) {
            $user = $this->me->getModel();
            $opinion = new \Model\Opinion();
            $opinion->setContent($_POST['opinion']);
            $opinion->setRating($_POST['score']);
            $opinion->setDate(new \DateTime());
            $opinion->setUser($user);
            $this->em->persist($opinion);
            $this->em->flush();

            \Notify::success('Twoja opinia zostanie dodana do księgi gości zaraz po weryfikacji.');
        }
        $opinions = $this->em->getRepository('\Model\Opinion')->findBy(array('isActive' => '1', 'isVerified' => '1'));
        return array("opinions" => $opinions);
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
        {
            $user = $this->em->getRepository('\Model\User')->findOneBy(array('username' => $_POST['username']));
            if($user != null)
                return array("values" =>  $_POST,"usernameIsInDatabase" => true);
            $msg = $this->homeUtil->registerForm($_POST);
        }
        return array();
    }

    /**
     * Reservation
     * @Route(/reservation)
     */
    public function reservation() {

        return array("data" => $_POST);
    }

    /**
     * Reservation
     * @Route(/getrooms/{from}/{to}/{param})
     */
    public function getRooms($from = null, $to = null, $param = null) {
        if ($from != null && $to != null) {
            $from = new \DateTime($from);
            $to = new \DateTime($to);
            $qb = $this->em->createQueryBuilder();
            $reservations = $qb
                    ->select('rr.id')
                    ->from('\Model\Reservation', 're')
                    ->join('re.rooms', 'rr')
                    ->where('(re.fromDate >= ?1 AND re.fromDate < ?2) OR (re.toDate >= ?2 AND re.fromDate < ?2) OR (re.fromDate < ?1 AND re.toDate > ?1)')
                    ->setParameters(array(1 => $from, 2 => $to))
                    ->getQuery()
                    ->getResult();
        }
        if (isset($reservations[0])) {
            $rooms = $this->em->createQueryBuilder()->select('ro')
                    ->from('\Model\Room', 'ro')
                    ->where($qb->expr()->notIn('ro.id', array_column($reservations, 'id')))
                    ->getQuery()
                    ->getResult();
        } else {
            $rooms = $this->em->getRepository('\Model\Room')->findAll();
        }

        return array("rooms" => $rooms, "toilet" => $_GET['toilet'], "balcony" => $_GET['balcony']);
    }

    /**
     * Reservation Pay
     * @Route(/reservationpay)
     */
    public function reservationPay() {

        $rooms = $this->em->createQueryBuilder()->select('ro')
                ->from('\Model\Room', 'ro')
                ->where($this->em->createQueryBuilder()->expr()->in('ro.id', $_POST['room']))
                ->getQuery()
                ->getResult();

        $allCost = 0;
        // dodanie rezerwacji do BD
        $re = new \Model\Reservation();
        $re->setFromDate(new \DateTime($_POST['fromDate']));
        $re->setToDate(new \DateTime($_POST['toDate']));
        $re->setReservationDate(new \DateTime());
        foreach ($rooms as $room) {
            $re->addRoom($room);
            $allCost += $room->getCost();
        }
        if (isset($_POST['userId']) && is_numeric($_POST['userId'])) {
            $user = $this->em->getRepository('\Model\\User')->find($_POST['userId']);
            $re->setUser($user);
        } else {
            $re->setGuest($_POST['name']);
        }
        $this->em->persist($re);
        $this->em->flush();


        // tpay.com
        $tid = $this->em->getRepository('\Model\Setting')->findOneBy(array('name' => 'tid'))->value();
        $crc = $re->getId();
        $tkey = $this->em->getRepository('\Model\Setting')->findOneBy(array('name' => 'tkey'))->value();
        $md5 = md5($tid . $allCost . $crc . $tkey);


        return array("data" => $_POST, 'rooms' => $rooms, 'rid' => $re->getId(), 'cost' => $allCost, 'md5' => $md5, 'crc' => $crc);
    }

    /**
     * Reservation Pay
     * @Route(/reservationpayconfirm/{id})
     */
    public function reservationPayConfirm($id = null) {
        \Notify::success('Płatość zaksięgowana!');



        return array();
    }

    /**
     * Reservation Pay
     * @Route(/reservationpayerr/{id})
     */
    public function reservationPayErr($id = null) {
        \Notify::error('Rezerwacja anulowana!');

        return array();
    }

    /**
     * Potwierdzenie dla tPay.com
     * @Route(/tpayconfirm)
     */
    public function tPayConfirm() {
        // sprawdzenie adresu IP oraz występowania zmiennych POST
        if ($_SERVER['REMOTE_ADDR'] == '195.149.229.109' && !empty($_POST)) {
            //$id_sprzedawcy = $_POST['id'];
            $status_transakcji = $_POST['tr_status'];
            //$id_transakcji = $_POST['tr_id'];
            //$kwota_transakcji = $_POST['tr_amount'];
            //$kwota_zaplacona = $_POST['tr_paid'];
            $blad = $_POST['tr_error'];
            //$data_transakcji = $_POST['tr_date'];
            //$opis_transakcji = $_POST['tr_desc'];
            $id = $_POST['tr_crc'];
            //$email_klienta = $_POST['tr_email'];
            //$suma_kontrolna = $_POST['md5sum'];
            // sprawdzenie stanu transakcji
            if ($status_transakcji == 'TRUE' && $blad == 'none') {
                $reservation = $this->em->getRepository('\Model\Reservation')->find($id);
                $reservation->setPaid(true);
                $this->em->flush();
            } else {
                $reservation = $this->em->getRepository('\Model\Reservation')->find($id);
                $this->em->remove($reservation);
                $this->em->flush();
            }
        }
        //echo 'TRUE'; // odpowiedź dla serwera o odebraniu danych
        return array();
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

    /**
     * @Route(/removeNotif)
     */
    public function removeNotif() {
        $n = $this->em->getRepository('\Model\\Notification')->find($_POST['id']);
        $this->em->remove($n);
        $this->em->flush();

        return array("info" => "brak");
    }

}
