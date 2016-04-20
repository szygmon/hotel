<?php

class GlobalUtil {

    /** @var \User\Me */
    protected $Me;

    /** @var \Doctrine\ORM\EntityManager */
    protected $em;

    /**
     * @param \User\Me $Me
     */
    function __construct() {
        $this->Me = Di::get('Me');
        $this->em = Di::get('em');
    }

    public function getSetting($name) {
        return $this->em->getRepository('\Model\Setting')->find($name);
    }
    
    public function getSettings() {
        $settings = null;
        $s = $this->em->getRepository('\Model\Setting')->findAll();
        foreach ($s as $setting) {
            $settings[$setting->getName()] = $setting->getValue();
        }
        return $settings;
    }

}
