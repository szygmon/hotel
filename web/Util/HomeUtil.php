<?php

class HomeUtil {

    /** @var \User\Me */
    protected $Me;

    /** @var \Doctrine\ORM\EntityManager */
    protected $em;

    function __construct() {
        $this->Me = Di::get('Me');
        $this->em = Di::get('em');
    }

    public function registerForm($post) {

        $user = new \Model\User();
        $user->setGivenName($_POST['givenName']);
        $user->setFamilyName($_POST['familyName']);
        $user->setUsername($_POST['username']);
        $user->setEmail($_POST['email']);
        $user->setPassword($_POST['pass']);
        $user->setPhone($_POST['phone']);
        $this->em->persist($user);
        $this->em->flush();

        return \Notify::success('Zarejestrowano pomyślnie! Możesz się teraz <a href="signin">zalogować</a>.');
    }

    public function updateForm($post) {

        $user = $this->em->getRepository('Model\\User')->findOneBy(array('username' => $_POST['username']));
        $user->setGivenName($_POST['givenName']);
        $user->setFamilyName($_POST['familyName']);
        $user->setEmail($_POST['email']);
        $user->setPhone($_POST['phone']);
        if (isset($_POST['pass']) && $_POST['pass'] != '' && $_POST['pass'] == $_POST['passconfirm'])
            $user->setPassword($_POST['pass']);
        //$this->em->persist($user);
        $this->em->flush();

        return \Notify::success('Dane zaktualizowano');
    }

    public function registerFormAdmin($post) {
        // Role
        $adminRole = new \Model\Role;
        $adminRole->setName('admin');
        $this->em->persist($adminRole);

        $user = new \Model\User();
        $user->setUsername($_POST['username']);
        $user->setEmail($_POST['email']);
        $user->setPassword($_POST['pass']);
        $user->addRole($adminRole);
        $this->em->persist($user);
        $this->em->flush();

        return \Notify::success('Zarejestrowano pomyślnie');
    }

    public function loginForm($post) {
        $login = $this->Me->login($_POST['username'], $_POST['password']);

        if ($login) {
            $this->Me->getModel()->setLastLogin(new DateTime);
            Di::get('em')->flush();
            if ($this->Me->auth('admin'))
                Di::get('Router')->redirect('Admin/index');
            else
                Di::get('Router')->redirect('Home/index');
        } 
            
        return \Notify::error('Błędny login i/lub hasło.');
    }

}
