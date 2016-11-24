<?php

namespace AppBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 */
class Profileable {

	private $profileId;

	public function __construct($options) {

		if (isset($options['profileId'])) {
			$this->profileId = $options['profileId'];
		} else {
			throw new \Exception(
				sprintf("@%s requires a mandatory profileId parameter", self::class)
			);
		}
	}

	/**
	 * Get profileId
	 * @return
	 */
	public function getProfileId() {
		return $this->profileId;
	}

	/**
	 * Set profileId
	 * @return $this
	 */
	public function setProfileId($profileId) {
		$this->profileId = $profileId;
		return $this;
	}

}