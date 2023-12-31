<?php

namespace User;

use \Conf;
use \Core\Cookie;

class Me {

    /**
     * @var \Model\User
     */
    private $model = false;
    private $cookie = null;
    private $auth = array();

    function __construct() {
        $this->checkUser();
    }

    public function isLogged() {
        return !!$this->model;
    }

    public function getModel() {
        return $this->model;
    }

    public function auth($role) {
		if(!$this->model)
			return FALSE;
        if (!count($this->auth))
            $this->auth = $this->model->auth();

        return in_array($role, $this->auth);
    }

    public function checkUser() {
        $cookie = $this->cookie();
        if ((!isset($_SESSION['user.email']) || !isset($_SESSION['user.auth'])) && $cookie->getIsset()) {
            $id = $cookie->get('id');
            $ts = $cookie->get('ts');
            $auth = $cookie->get('auth');
            if ($auth === crypt($id . $ts . Conf::get('nc.salt'), $auth)) {
                session_destroy();
                session_id($id);
                session_start();
            }
        }

        if (isset($_SESSION['user.email']) && isset($_SESSION['user.auth'])) {
            $this->setUser($_SESSION['user.email']);
            if ((!$this->model) || !($_SESSION['user.auth'] === crypt($this->model->getPassword(), $_SESSION['user.auth']))) {
                $this->logout();
            }
            $cookie->setSave(TRUE);
        }
    }

    public function login($login, $pass) {
        if ($this->setUser($login, $pass)) {
            session_regenerate_id(TRUE);
            $_SESSION['user.email'] = $this->model->getEmail();
            $_SESSION['user.auth'] = crypt($this->model->getPassword());
            $cookie = $this->cookie();
            $sId = session_id();
            $time = time();
            $cookie->set('id', $sId);
            $cookie->set('ts', $time);
            $cookie->set('auth', crypt($sId . $time . Conf::get('nc.salt')));
            return true;
        }
        return false;
    }

    public function logout() {
        unset($_SESSION['user.email']);
        unset($_SESSION['user.auth']);
        $cookie = $this->cookie();
        $cookie->delete();
        $this->model = NULL;
    }

    private function setUser($login, $pass = FALSE) {
        $em = \Di::get('em');

        $model = $em->getRepository('Model\\User')
                ->createQueryBuilder('u')
                ->andWhere('u.email = :login OR u.username = :login')
                ->setParameter('login', $login)
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();
        if (!$model || $pass && !($model->getPassword() == crypt($pass, $model->getPassword())))
            return false;

        $this->model = $model;
        return true;
    }

    public function cookie() {
        if (is_null($this->cookie))
            $this->cookie = Cookie::create(Conf::get('nc.title') . "_session");
        return $this->cookie;
    }

    public function getNotifications() {
        $em = \Di::get('em');

        $notifications = $em->getRepository('\Model\\Notification')->findBy(array('user' => $this->model), array('id' => 'DESC'));
        $count = count($notifications);

        return array('notifs' => $notifications, 'count' => $count);
    }
}
