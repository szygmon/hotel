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

        $this->em->persist($user);
        $this->em->flush();

        \Notify::success('Dodano użytkownika.');
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
        $this->em->flush();

        \Notify::success('Zaktualizowano dane użytkownika.');
    }

    public function deleteUser($id) {
        if (!is_numeric($id))
            return;
        $user = $this->em->getRepository('\Model\User')->find($id);
        $user->setIsActive(false);
        $this->em->flush();

        \Notify::success('Usunięto użytkownika.');
    }
    
    public function getMails() {
        $mails = $this->em->getRepository('\Model\Mail')->findBy(array('isActive' => 1, 'isRead' => 0), array('id' => 'DESC'));
        
        return $mails;
    }
}
