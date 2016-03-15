<?php

namespace Core;

use \Conf;

class Cookie {

    private $name;
    private $cookie = array();
    private $isset = FALSE;
    private $isEdited = FALSE;
    private $timeOut;
    private static $objects = array();

    public static function create($name = NULL, $save = FALSE) {
        $cookie = new Cookie($name, $save);
        self::$objects[] = $cookie;
        return $cookie;
    }

    public static function destruct() {
        foreach (self::$objects as $cookie)
            $cookie->save();
    }

    private function __construct($name = NULL, $save = FALSE) {
        if (!$name)
            $name = Conf::get('nc.title') . "_cookie";

        $this->isEdited = $save;
        $this->name = $name;
        $this->timeOut = time() + Conf::get('cookie.lifetime');

        if (isset($_COOKIE[$name])) {
            $this->isset = TRUE;
            $this->cookie = json_decode($_COOKIE[$name], true);
        }
    }

    public function save() {
        if ($this->isEdited) {
            setcookie($this->name, json_encode($this->cookie), $this->timeOut, '/');
            $this->isEdited = FALSE;
        }
    }

    public function get($name = NULL) {
        if (is_null($name))
            return $this->cookie;
        if (isset($this->cookie[$name]) && ($this->cookie[$name]['time'] >= time() || $this->cookie[$name]['time'] === null))
            return $this->cookie[$name]['data'];
        return false;
    }

    public function getIsset() {
        return $this->isset;
    }

    public function setSave($save) {
        return $this->isEdited = $save;
    }

    public function set($name, $data, $time = null) {
        if (!$this->isEdited)
            $this->isEdited = TRUE;

        $this->cookie[$name] = array(
            'data' => $data,
            'time' => $time
        );
    }

    public function delete() {
        $this->isEdited = FALSE;
        setcookie($this->name, null, time(), '/');
    }

    private function cookiePolicy() {
        // MAKE ME
    }

}
