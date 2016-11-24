<?php

namespace AppBundle\Service;

use Symfony\Component\Stopwatch\Stopwatch;

class StopwatchProvider {

	protected $stopwatcher;

	public function __construct() {
		$this->stopwatcher = new Stopwatch();
	}

	/**
	 * Get stopwatcher
	 * @return
	 */
	public function getStopwatcher() {
		return $this->stopwatcher;
	}

	/**
	 * Set stopwatcher
	 * @return $this
	 */
	public function setStopwatcher($stopwatcher) {
		$this->stopwatcher = $stopwatcher;
		return $this;
	}
}