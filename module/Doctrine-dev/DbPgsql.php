<?php

use \Conf;
use \Di;

class DbPgsql {

	public static function dropAll() {
		$schemas = Db::sql("SELECT format('DROP SCHEMA %I CASCADE', nspname) FROM pg_namespace join pg_user on(usesysid=nspowner) where user=usename;");
		foreach ($schemas as $sql) {
			Db::exec($sql['format']);
		}

		Db::exec("CREATE SCHEMA public");
	}

}
