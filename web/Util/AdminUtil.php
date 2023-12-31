<?php

class AdminUtil {

    /** @var \User\Me */
    protected $Me;

    /** @var \Doctrine\ORM\EntityManager */
    protected $em;

    function __construct() {
        $this->Me = Di::get('Me');
        $this->em = Di::get('em');
    }

    public function addUser($post) {
        $user = new \Model\User();
        $user->setUsername($post['username']);
        $user->setGivenName($post['givenName']);
        $user->setFamilyName($post['familyName']);
        if ($post['password'] == '')
            $user->setPassword('qwerty');
        else
            $user->setPassword($post['password']);
        $user->setEmail($post['email']);
        $user->setPhone($post['phone']);
        if (isset($post['roles'][0])) {
            foreach ($post['roles'] as $r) {
                $role = $this->em->getRepository('\Model\Role')->findOneBy(array('name' => $r));
                $user->addRole($role);
            }
        }

        $this->em->persist($user);
        $this->em->flush();

        \Notify::success('Dodano użytkownika');
    }

    public function updateUser($post, $id = null) {
        if (!is_numeric($id))
            return;
        $user = $this->em->getRepository('\Model\User')->find($id);
        $user->setUsername($post['username']);
        $user->setGivenName($post['givenName']);
        $user->setFamilyName($post['familyName']);
        if ($post['password'] != '')
            $user->setPassword($post['password']);
        $user->setEmail($post['email']);
        $user->setPhone($post['phone']);
        $user->getRoles()->clear();
        if (isset($post['roles'][0])) {
            foreach ($post['roles'] as $r) {
                $role = $this->em->getRepository('\Model\Role')->findOneBy(array('name' => $r));
                if (!$user->getRoles()->contains($role))
                    $user->addRole($role);
            }
        }

        $this->em->flush();

        \Notify::success('Zaktualizowano dane użytkownika');
    }

    public function deleteUser($id) {
        if (!is_numeric($id)) {
            \Notify::error("Błąd");
            return;
        }
        if ($this->Me->getModel()->getId() == $id) {
            \Notify::error("Nie można usunąć samego siebie!");
            return;
        }

        $user = $this->em->getRepository('\Model\User')->find($id);
        $user->setIsActive(false);
        $this->em->flush();

        \Notify::success('Usunięto użytkownika');
    }

    public function getNewMails() {
        $mails = $this->em->getRepository('\Model\Mail')->findBy(array('isActive' => 1, 'isRead' => 0), array('id' => 'DESC'));

        return $mails;
    }

    public function getNewOpinions() {
        $opinions = $this->em->getRepository('\Model\Opinion')->findBy(array('isActive' => 1, 'isVerified' => 0));

        return $opinions;
    }

    public function sendMail($post) {
        if (!isset($post['sendMail'])) {
            return;
        }
        
        if ($post['emailto'] == '0') {
            \Notify::error('Wybierz adres email');
            return;
        }
        
        $headers = 'From: ' . $this->em->getRepository('\Model\Setting')->find('email')->getValue();
        mail($post['emailto'], $post['subject'], $post['message'], $headers);

        \Notify::success('Wiadomość została wysłana');
    }

}
