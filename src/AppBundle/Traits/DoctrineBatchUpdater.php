<?php

namespace AppBundle\Traits;

use \Doctrine\ODM\MongoDB\DocumentManager;

class DoctrineBatchUpdater {

	/**
	 *
	 * @var DocumentManager
	 */
	protected $documentManager = null;

	/**
	 *
	 * @var integer
	 */
	protected $batchSize = null;

	/**
	 *
	 * @var integer
	 */
	protected $currentSize = 0;

	protected $enable = true;

	public function __construct(
		DocumentManager $documentManager,
		$batchSize) {
		$this->documentManager = $documentManager;
		$this->batchSize = abs($batchSize);
		$this->currentSize = 0;
	}

	/**
	 *
	 * @param boolean $enable
	 * @return self
	 */
	public function enable($enable = true) {
		$this->enable = true == $enable;
		return $this;
	}

	public function isEnable() {
		return $this->enable;
	}

	/**
	 *
	 * @param mixed $document
	 * @return self;
	 */
	public function update($document) {
		if (true != $this->isEnable()) {
			return $this;
		}
		$this->currentSize++;
		$this->documentManager->persist($document);

		return $this;
	}

	/**
	 *
	 * @param mixed $document
	 * @return self;
	 */
	public function remove($document) {
		if (true != $this->isEnable()) {
			return $this;
		}
		$this->currentSize++;
		$this->documentManager->remove($document);

		return $this;
	}

	/**
	 *
	 * @return self
	 */
	public function synchronize($force = true) {
		if (true != $this->isEnable()) {
			return $this;
		}
		return $this->flush($force);
	}

	/**
	 *
	 * @return self
	 */
	public function synchronizeAndClear($force = true) {
		if (true != $this->isEnable()) {
			return $this;
		}
		return $this->flushAndClear($force);
	}

	/**
	 *
	 * @param boolean $force
	 * @return self
	 */
	protected function flush($force = false) {
		if ((false === $force)
			&& (true != $this->isFlushable())) {
			return $this;
		}

		$this->documentManager->flush();
		return $this;
	}

	/**
	 *
	 * @param boolean $force
	 * @return self
	 */
	protected function flushAndClear($force = false) {
		if ((false === $force)
			&& (true != $this->isFlushable())) {
			return $this;
		}

		$this->documentManager->flush();
		$this->clear();
		return $this;
	}

	/**
	 *
	 * @param boolean $force
	 * @return self
	 */
	public function clear() {

		$this->documentManager->clear();

		return $this;
	}

	public function getUoWSIze() {
		return $this->documentManager->getUnitOfWork()->getSize();
	}

	/**
	 *
	 * @return boolean
	 */
	protected function isFlushable() {
		return $this->batchSize < 1
		? true
		: (0 === ($this->currentSize % $this->batchSize));
	}
}
