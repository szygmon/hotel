<?php


class DbMysql {

    public static function dropAll() {
        \Db::clear();
    }

}
