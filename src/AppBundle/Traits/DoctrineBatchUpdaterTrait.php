<?php

namespace AppBundle\Traits;

use Doctrine\ODM\MongoDB\DocumentManager;
use MInside\InsideBoard\CoreBundle\Utils\Helpers\Object\UpdaterStack;

trait DoctrineBatchUpdaterTrait {
	/**
	 * @return DocumentManager
	 */
	abstract protected function getDocumentManager();

	/**
	 * @return integer
	 */
	abstract protected function getBatchSize();

	/**
	 * @return UpdaterStack
	 */
	protected function getBatchUpdater() {
		static $result = null;

		if (null === $result) {
			$result = new DoctrineBatchUpdater(
				$this->getDocumentManager(),
				$this->getBatchSize());
		}

		return $result;
	}

}