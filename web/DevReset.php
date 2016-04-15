<?php

use \Model\School;

class DevReset {

    /** @var \Doctrine\ORM\EntityManager */
    protected $em;

    public function __construct() {
        $this->em = Di::get('em');
    }

    public function update() {
        $um = new UserManager;
        $r = array();
        $r += $um->setup();
        return $r;
    }

    public function dummyData() {
        $userManager = new UserManager;

        // Role
        $adminRole = new \Model\Role;
        $adminRole->setName('admin');
        $this->em->persist($adminRole);

        // Admin
        $user = new \Model\User();
        $user->setEmail('admin@admin.pl');
        $user->setUsername('admin');
        $user->setPassword('pass');
        $user->setGivenName('Admin');
        $user->setFamilyName('Test');
        $user->addRole($adminRole);
        $this->em->persist($user);
        $this->em->flush();

        // Settings
        $settings[0] = new \Model\Setting();
        $settings[0]->setName('rules');
        $settings[0]->setValue('');
        $this->em->persist($settings[0]);
        $settings[1] = new \Model\Setting();
        $settings[1]->setName('tid');
        $settings[1]->setValue('');
        $this->em->persist($settings[1]);
        $settings[2] = new \Model\Setting();
        $settings[2]->setName('tkey');
        $settings[2]->setValue('');
        $this->em->persist($settings[2]);
        $this->em->flush();

        return $r;
    }

}
