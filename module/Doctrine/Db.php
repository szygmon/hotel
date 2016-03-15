<?php

use \Di;

class Db {

	public static function sql($sql) {
		return Di::get('em')->getConnection()->query($sql)->fetchAll();
	}

	public static function exec($sql) {
		return Di::get('em')->getConnection()->exec($sql);
	}

	public static function install($prefix = 'ver-') {
		$files = glob(ABSPATH . 'sql/' . $prefix . '*.sql');

		$r = array();
		foreach ($files as $file) {
			$lines = 1;
			$sqls = self::split(file_get_contents($file));

			try {
				foreach ($sqls as $sql) {
					if (trim($sql))
						self::exec($sql);
					++$lines;
				}
			} catch (Exception $e) {
				if (strpos(strtolower($e->getMessage()), 'duplicate') === false) {
					$r[$file]['line'] = $lines;
					$r[$file]['message'] = $e->getMessage();
				}
			}
		}

		return $r;
	}

	private static function split($sql) {
		$sql = str_replace("\r\n", "\n", $sql);
		$sql = str_replace("\r", "\n", $sql);
		$sql = preg_replace("/\n{2,}/", "\n\n", $sql);

		$r = array();
		$buf = explode(";\n", $sql);
		foreach ($buf as $row) {
			$r[] = $row;
		}
		return $r;
	}

}
