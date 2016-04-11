<?php

use \Conf;

class DbMysql {

    public static function dropAll() {
        Db::exec("DROP DATABASE" + \Conf::get('db.name'));
        Db::exec("CREATE DATABASE" + \Conf::get('db.name'));
    }

}
