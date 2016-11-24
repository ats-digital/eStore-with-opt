<?php

namespace AppBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;

class ProductRepository extends DocumentRepository {

	public function getAllTags() {

		return $this->createQueryBuilder()
			->distinct('tag')
			->getQuery()
			->execute()
			->toArray()
		;
	}

	public function getSomeProducts($size) {

		return $this->createQueryBuilder()
			->limit($size)
			->hydrate(true)
			->getQuery()
			->execute()
			->toArray()
		;
	}

}

?>