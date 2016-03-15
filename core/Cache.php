<?php

namespace Core;

use Conf;

class Cache {

	private $memcached;

	public function __construct() {
//		$this->memcached = new \Memcached;
//		$this->memcached->addServer(Conf::get('cache.host'), Conf::get('cache.port'));
	}

	public function get($key) {
		return $this->memcached->get($key);
	}

	public function set($key, $value, $expiration = NULL) {
		if (!$expiration)
			$expiration = Conf::get('cache.lifetime');

		return $this->memcached->set($key, $value, time() + $expiration);
	}

	public function clear($key = NULL) {
		if (!$key)
			return $this->memcached->flush();
		return $this->memcached->delete($key);
	}

	
}
