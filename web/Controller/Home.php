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
     * Register
     * @Route(/register)
     */
    public function register() {
        if (isset($_POST['username']))
            $msg = $this->homeUtil->registerForm($_POST);
        return array("salesPage" => true);
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
     * @Route(/getrooms/{from}/{to})
     */
    public function getRooms($from = null, $to = null) {
        /*$r = $this->em->getRepository('\Model\Room')->find(2);
        $re = new \Model\Reservation();
        $re->setFromDate(new \DateTime());
        $re->setToDate(new \DateTime('2016-04-10'));
        $re->addRoom($r);
        $re->setGuest('');
        $this->em->persist($re);
        $this->em->flush();*/
        
        
        if ($from != null && $to != null) {
            $from = new \DateTime($from);
            $to = new \DateTime($to);
            $qb = $this->em->createQueryBuilder();
            $reservations = $qb
                    ->select('rr.id')
                    ->from('\Model\Reservation', 're')
                    ->join('re.rooms', 'rr')
                    ->where('(re.fromDate < ?1 AND re.toDate <= ?2 AND re.toDate > ?1) OR (re.fromDate >= ?1 AND re.fromDate < ?2)')
                    ->setParameters(array(1 => $from, 2 => $to))
                    ->getQuery()
                    ->getResult();
            
            /*foreach ($reservations as $r) {
                $tmp = $r->getRooms();
                foreach ($tmp as $room) {
                    $notIn[] = $room->getId();
                }
            }*/
            
         //foreach ($reservations as $reserw) {
             var_dump($reservations);
         //}
            


            $rooms = $qb->select('ro')
                    ->from('\Model\Room', 'ro')                   
                    ->where($qb->expr()->notIn('ro.id', $reservations))
                    ->getQuery()
                    ->getResult();
            var_dump($rooms);
        } else {
            $rooms = null;
        }
        
        return array("rooms" => $rooms);
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
