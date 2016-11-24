<?php

namespace AppBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 */

class Cacheable {

	const DEFAULT_EXPIRATION_TIME = 3000;

	private $key;

	public function __construct($options) {

		if (isset($options['key'])) {
			$this->key = $options['key'];
		} else {
			throw new \Exception(
				sprintf("@%s requires a mandatory cacheId parameter", self::class)
			);
		}
	}

	/**
	 * Get key
	 * @return string
	 */
	public function getKey() {
		return $this->key;
	}

	/**
	 * Set key
	 * @return $this
	 */
	public function setKey($key) {
		$this->key = $key;
		return $this;
	}

}