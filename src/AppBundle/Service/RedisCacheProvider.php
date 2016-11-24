<?php

namespace AppBundle\Service;

use Symfony\Component\Cache\Adapter\RedisAdapter;

class RedisCacheProvider {

	protected $cache;

	public function __construct($redisConnectionString) {
		$this->cache = new RedisAdapter(RedisAdapter::createConnection($redisConnectionString));
	}

	public function getCache() {
		return $this->cache;
	}
}