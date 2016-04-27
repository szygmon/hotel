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
        $receptionistRole = new \Model\Role;
        $receptionistRole->setName('receptionist');
        $this->em->persist($receptionistRole);

        // Admin
        $user = new \Model\User();
        $user->setEmail('admin@admin.pl');
        $user->setUsername('admin');
        $user->setPassword('pass');
        $user->setGivenName('Admin');
        $user->setFamilyName('Test');
        $user->addRole($adminRole);
        $this->em->persist($user);

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
        $settings[3] = new \Model\Setting();
        $settings[3]->setName('email');
        $settings[3]->setValue('');
        $this->em->persist($settings[3]);
        $settings[4] = new \Model\Setting();
        $settings[4]->setName('sitename');
        $settings[4]->setValue('');
        $this->em->persist($settings[4]);
        $settings[5] = new \Model\Setting();
        $settings[5]->setName('siteurl');
        $settings[5]->setValue('');
        $this->em->persist($settings[5]);
        $settings[6] = new \Model\Setting();
        $settings[6]->setName('cron');
        $settings[6]->setValue(0);
        $this->em->persist($settings[6]);
        $settings[7] = new \Model\Setting();
        $settings[7]->setName('c_reservation_time');
        $settings[7]->setValue('- 1 day');
        $this->em->persist($settings[7]);
        $settings[8] = new \Model\Setting();
        $settings[8]->setName('payment_online');
        $settings[8]->setValue(1);
        $this->em->persist($settings[8]);
        
        $this->em->flush();

        return $r;
    }

}
