<?php

namespace AppBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;

abstract class StdClassNormalizer {

	public function toStdClass() {

		$properties = (new \ReflectionClass($this))->getProperties();

		$result = new \stdClass;

		foreach ($properties as $property) {

			$invokedProperty = $this->{$property->getName()};

			if ($invokedProperty instanceof ArrayCollection) {

				$result->{$property->getName()} = [];

				foreach ($invokedProperty->toArray() as $invokedSubProperty) {
					$result->{$property->getName()}[] = $invokedSubProperty->toStdClass();
				}

			} else {
				$result->{$property->getName()} = $invokedProperty;
			}

		}

		unset($result->id);

		return $result;
	}

}