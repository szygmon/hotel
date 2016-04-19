<?php

namespace Controller;

class Admin {

    /** @var \User\Me */
    protected $me;

    /** @var \Doctrine\ORM\EntityManager */
    protected $em;

    /** @var \HomeUtil */
    protected $homeUtil;

    /** @var \AdminUtil */
    protected $adminUtil;

    /**
     * @param \User\Me $Me
     * @param \Doctrine\ORM\EntityManager $em
     * @param \HomeUtil $HomeUtil
     * @param \AdminUtil $AdminUtil
     */
    function __construct($Me, $em, $HomeUtil, $AdminUtil) {
        $this->me = $Me;
        $this->em = $em;
        $this->homeUtil = $HomeUtil;
        $this->adminUtil = $AdminUtil;

        \Di::get('Template')->addTwigGlobals(['newMails' => $this->adminUtil->getNewMails()]);
        \Di::get('Template')->addTwigGlobals(['newOpinions' => $this->adminUtil->getNewOpinions()]);
    }

    /**
     * Logowanie do panelu administracyjnego
     * @Route(/admin)
     * @param \User\Me $Me
     * @param \Core\Router $Router
     */
    public function admin($Me, $Router) {
        if (isset($_POST['username']))
            $msg = $this->homeUtil->loginForm($_POST);

        if ($Me->auth('admin') || $Me->auth('receptionist'))
            $Router->redirect('Admin/index');

        return array('msg' => $msg);
    }

    /**
     * Kokpit
     * @Route(/admin/index)
     * @param \User\Me $Me
     * @param \Core\Router $Router
     */
    public function index($Me, $Router) {
        if (!$Me->auth('admin') && !$Me->auth('receptionist'))
            $Router->redirect('Admin/admin');

        $users = $this->em->createQueryBuilder()->select('COUNT(u.id)')->from('\Model\User', 'u')->where('u.isActive = 1')->getQuery()->getSingleScalarResult();
        $reservationsConfirm = $this->em->createQueryBuilder()->select('COUNT(r.id)')->from('\Model\Reservation', 'r')->where('r.paid = 1')->getQuery()->getSingleScalarResult();
        $reservationsNotConfirm = $this->em->createQueryBuilder()->select('COUNT(r.id)')->from('\Model\Reservation', 'r')->where('r.paid = 0')->getQuery()->getSingleScalarResult();
        $rooms = $this->em->createQueryBuilder()->select('COUNT(r.id)')->from('\Model\Room', 'r')->getQuery()->getSingleScalarResult();


        $date = new \DateTime(date('Y-m-d'));
        $qb = $this->em->createQueryBuilder();
        $reserved = $qb
                ->select('rr.id, rr.name, rr.number')
                ->from('\Model\Reservation', 're')
                ->join('re.rooms', 'rr')
                ->where('(re.fromDate = ?1) OR (re.toDate > ?1 AND re.fromDate < ?1)')
                ->setParameters(array(1 => $date))
                ->getQuery()
                ->getResult();
        
        //var_dump($reserved);

        if (isset($reserved[0])) {
            $available = $this->em->createQueryBuilder()->select('ro')
                    ->from('\Model\Room', 'ro')
                    ->where($qb->expr()->notIn('ro.id', array_column($reserved, 'id')))
                    ->getQuery()
                    ->getResult();
        } else {
            $available = $this->em->getRepository('\Model\Room')->findAll();
        }


        return array(
            'users' => $users,
            'reservationsConfirm' => $reservationsConfirm,
            'reservationsNotConfirm' => $reservationsNotConfirm,
            'rooms' => $rooms,
            'reserved' => $reserved,
            'available' => $available
        );
    }

    /**
     * Pokoje - lista i akcje dodawania/edycji/usuwania
     * @Route(/admin/rooms/{action}/{id})
     * @param \User\Me $Me
     * @param \Core\Router $Router
     */
    public function rooms($Me, $Router, $action = null, $id = null) {
        if (!$Me->auth('admin'))
            $Router->redirect('Admin/admin');

        if ($action == 'del' && is_numeric($id)) {
            $room = $this->em->getRepository('\Model\Room')->find($id);
            $room->setIsActive(0);
            $this->em->flush();

            \Notify::success('Pokój został usunięty z bazy danych');
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

            \Notify::success('Zaktualizowano pokój w bazie danych');
        }

        $rooms = $this->em->getRepository('\Model\Room')->findAll();

        return array('rooms' => $rooms);
    }

    /**
     * Edycja pokoju
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
     * Ustawienia tPay.com
     * @Route(/admin/tpay/{action})
     * @param \User\Me $Me
     * @param \Core\Router $Router
     */
    public function tpay($Me, $Router, $action = null) {
        if (!$Me->auth('admin'))
            $Router->redirect('Admin/admin');

        $tid = $this->em->getRepository('\Model\Setting')->find('tid');
        $tkey = $this->em->getRepository('\Model\Setting')->find('tkey');
        if ($action == 'save') {
            $tid->setValue($_POST['tid']);
            $tkey->setValue($_POST['tkey']);
            $this->em->flush();

            \Notify::success('Zapisano ustawienia tPay.com');
        }

        return array('tid' => $tid->getValue(), 'tkey' => $tkey->getValue());
    }

    /**
     * Rezerwacje - lista i akcje dodania/edycji/usuwania/zmiany stanu opłacenia
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
        } else if ($action == 'noPaid' && is_numeric($id)) {
            $res = $this->em->getRepository('\Model\Reservation')->find($id);
            $res->setPaid(0);
            $this->em->flush();

            \Notify::success('Zaktualizowano status na rezerwacje nieopłaconą');
        } else if ($action == 'paidConfirm' && is_numeric($id)) {
            $res = $this->em->getRepository('\Model\Reservation')->find($id);
            $res->setPaid(1);
            $this->em->flush();

            \Notify::success('Zaktualizowano status na rezerwacje opłaconą');
        } else if ($action == 'old') {
            $reservations = $this->em->createQueryBuilder()
                    ->select('r')
                    ->from('\Model\Reservation', 'r')
                    ->where('r.toDate < ?1')
                    ->setParameter(1, new \DateTime(date('Y-m-d')))
                    ->orderBy('r.fromDate', 'DESC')
                    ->getQuery()
                    ->getResult();
        } else {
            $reservations = $this->em->createQueryBuilder()
                    ->select('r')
                    ->from('\Model\Reservation', 'r')
                    ->where('r.toDate >= ?1')
                    ->setParameter(1, new \DateTime(date('Y-m-d')))
                    ->orderBy('r.fromDate')
                    ->getQuery()
                    ->getResult();
        }

        return array('reservations' => $reservations, 'action' => $action);
    }

    /**
     * Edycja rezerwacji
     * @Route(/admin/editreservation/{id})
     * @param \User\Me $Me
     * @param \Core\Router $Router
     */
    public function editReservation($Me, $Router, $id = null) {
        if (!$Me->auth('admin') && !$Me->auth('receptionist'))
            $Router->redirect('Admin/admin');

        if (is_numeric($id)) {
            $reservation = $this->em->getRepository('\Model\Reservation')->find($id);
        } else
            $reservation = null;

        $users = $this->em->getRepository('\Model\User')->findAll();

        return array('reservation' => $reservation, 'users' => $users);
    }

    /**
     * Użytkownicy - lista i akcje dodania/edycji/usunięcia
     * @Route(/admin/users/{action}/{id})
     * @param \User\Me $Me
     * @param \Core\Router $Router
     */
    public function users($Me, $Router, $action = null, $id = null) {
        if (!$Me->auth('admin') && !$Me->auth('receptionist'))
            $Router->redirect('Admin/admin');
        switch ($action) {
            case 'add':
                $user = $this->em->getRepository('\Model\User')->findOneBy(array('username' => $_POST['username']));
                if ($user != null) {
                    \Notify::error('Użytkownik o podanej nazwie użytkownika już istnieje, wybierz inną nazwę.');
                    $Router->redirect("Admin/editUser", array("id" => null, "user" => $_POST));
                }
                $this->adminUtil->addUser($_POST);
                break;
            case 'updt':
                $this->adminUtil->updateUser($_POST, $id);
                break;
            case 'del':
                $this->adminUtil->deleteUser($id);
                break;
            default:
        }

        $users = $this->em->getRepository('\Model\User')->findBy(array('isActive' => '1'));

        return array('users' => $users);
    }

    /**
     * Edycja użytkownika
     * @Route("/admin/edituser/{id}")
     * @param \User\Me $Me
     * @param \Core\Router $Router
     */
    public function editUser($Me, $Router, $id = null, $user = null) {
        if (!$Me->auth('admin') && !$Me->auth('receptionist'))
            $Router->redirect('Admin/admin');

        if (($user == null ) && $id) {
            $user = $this->em->getRepository('\Model\User')->find($id);
        }

        return array('user' => $user);
    }

    /**
     * Widok czytanej wiadomości
     * @Route("/admin/mail/{id}")
     * @param \User\Me $Me
     * @param \Core\Router $Router
     */
    public function mail($Me, $Router, $id = null) {
        if (!$Me->auth('admin') && !$Me->auth('receptionist'))
            $Router->redirect('Admin/admin');

        if (is_numeric($id)) {
            $mail = $this->em->getRepository('\Model\Mail')->find($id);
            $mail->setIsRead(1);
            $this->em->flush();
        }

        return array('mail' => $mail);
    }

    /**
     * Wiadomości - lista i akcja odpowiedzi
     * @Route("/admin/mails/{action}/{id}")
     * @param \User\Me $Me
     * @param \Core\Router $Router
     */
    public function mails($Me, $Router, $action = null, $id = null) {
        if (!$Me->auth('admin') && !$Me->auth('receptionist'))
            $Router->redirect('Admin/admin');

        $mail = null;
        if (isset($_POST['reply']) && $action == 'reply' && is_numeric($id)) {
            $mail = $this->em->getRepository('\Model\Mail')->find($id);
            $headers = 'From: ' . $this->settings('email');
            mail($_POST['emailto'], $_POST['subject'], $_POST['message'], $headers);

            \Notify::success('Wiadomość została wysłana');
        }

        $mails = $this->em->getRepository('\Model\Mail')->findBy(array(), array('id' => 'DESC'));

        return array('mail' => $mail, 'allMails' => $mails);
    }

    /**
     * Ustawienia strony
     * @Route("/admin/sitesettings")
     * @param \User\Me $Me
     * @param \Core\Router $Router
     */
    public function siteSettings($Me, $Router) {
        if (!$Me->auth('admin'))
            $Router->redirect('Admin/admin');

        $email = $this->em->getRepository('\Model\Setting')->findOneBy(array('name' => 'email'));
        $rules = $this->em->getRepository('\Model\Setting')->find('rules');

        if (isset($_POST['save'])) {
            $email->setValue($_POST['email']);
            $rules->setValue($_POST['value']);
            $this->em->flush();

            \Notify::success('Zapisano ustawienia');
        }

        return array('email' => $email->getValue(), 'rules' => $rules->getValue());
    }

    /**
     * Opinie - lista i akcje akceptacji/usunięcia
     * @Route("/admin/opinions/{action}/{id}")
     * @param \User\Me $Me
     * @param \Core\Router $Router
     */
    public function opinions($Me, $Router, $action = null, $id = null) {
        if (!$Me->auth('admin') && !$Me->auth('receptionist'))
            $Router->redirect('Admin/admin');

        if ($action == 'accept' && is_numeric($id)) {
            $opinion = $this->em->getRepository('\Model\Opinion')->find($id);
            $opinion->setIsVerified(1);
            $this->em->flush();

            \Notify::success('Opublikowano');
        } else if ($action == 'remove' && is_numeric($id)) {
            $opinion = $this->em->getRepository('\Model\Opinion')->find($id);
            $opinion->setIsActive(0);
            $this->em->flush();

            \Notify::success('Usunięto');
        }

        $opinions = $this->em->getRepository('\Model\Opinion')->findBy(array('isActive' => 1));

        return array('opinions' => $opinions);
    }

    /**
     * Opinia - widok treści
     * @Route("/admin/opinion/{id}")
     * @param \User\Me $Me
     * @param \Core\Router $Router
     */
    public function opinion($Me, $Router, $id = null) {
        if (!$Me->auth('admin') && !$Me->auth('receptionist'))
            $Router->redirect('Admin/admin');

        $opinion = $this->em->getRepository('\Model\Opinion')->find($id);

        return array('opinion' => $opinion);
    }

}
