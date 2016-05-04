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

    public function updateAccount($post) {

        $user = $this->em->getRepository('Model\\User')->findOneBy(array('username' => $_POST['username']));
        $user->setGivenName($_POST['givenName']);
        $user->setFamilyName($_POST['familyName']);
        $user->setEmail($_POST['email']);
        $user->setPhone($_POST['phone']);
        if (isset($_POST['pass']) && $_POST['pass'] != '' && $_POST['pass'] == $_POST['passconfirm'])
            $user->setPassword($_POST['pass']);
        //$this->em->persist($user);
        $this->em->flush();
        $this->loginForm($_POST);
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
            if ($this->Me->getModel()->getIsActive() == 0)
                return \Notify::error('Użytkownik nieaktywny');
            $this->Me->getModel()->setLastLogin(new DateTime);
            Di::get('em')->flush();
            if ($this->Me->auth('admin'))
                Di::get('Router')->redirect('Admin/index');
            else if (Di::get('Router')->getController() != 'Home/account')
                Di::get('Router')->redirect('Home/index');
        } else
            return \Notify::error('Błędny login i/lub hasło.');
    }

    public function addContactMail($post) {
        $mail = new \Model\Mail();
        $mail->setName($post['name']);
        $mail->setEmail($post['email']);
        $mail->setPhone($post['phone']);
        $mail->setContent($post['content']);
        $mail->setMailDate(new \DateTime());

        $this->em->persist($mail);
        $this->em->flush();

        \Notify::success('Wiadomość została wysłana!');
    }

    public function remindPassword($post) {
        $data = new \stdClass();
        if (!isset($post['email'])) {
            $data->showGetMail = true;
            return $data;
        }
        $user = $this->em->getRepository('\Model\User')->findOneBy(array('email' => $post['email']));

        if ($user == null) {
            \Notify::error('Nie znaleziono użytkownika o podanym adresie email w naszej bazie');
            $data->showGetMail = true;
            return $data;
        }
        $newPassword = $this->GenerateRandomPassword(10);
        $user->setPassword($newPassword);
        $user->setIsActive(1); // aktywny nawet jeśli był nieaktywny
        $this->em->flush();
        $headers = 'From: ' . $this->settings('email');
        mail($post['email'], "Przypomnienie hasła w hotelu", "Twoje nowe hasło dostępu w hotelu to " . $newPassword, $headers);
        $data->showGetMail = false;
        \Notify::success('Nowe hasło zostało wysłane na podanym adres email');
        Di::get('Router')->redirect('Home/signin');
        return $data;
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

    private function GenerateRandomPassword($length) {
        return substr(md5(date("d.m.Y.H.i.s") . rand(1, 1000000)), 0, $length);
    }

}
