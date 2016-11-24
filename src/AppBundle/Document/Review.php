<?php

namespace AppBundle\Document;

use AppBundle\Document\Product;
use AppBundle\Document\StdClassNormalizer;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\ODM\MongoDB\Mapping\Annotations\EmbeddedDocument;
use JMS\Serializer\Annotation as JMS;

/**
 * @ODM\EmbeddedDocument
 * @JMS\ExclusionPolicy("all")
 */

class Review extends StdClassNormalizer {

	const MAX_RATING = 5;

	/**
	 * @ODM\Int
	 * @JMS\Type("integer")
	 * @JMS\Groups({"product.all", "product.single"})
	 * @JMS\Expose
	 */

	protected $rating;

	/**
	 * @ODM\String
	 * @JMS\Type("string")
	 * @JMS\Groups({"product.all", "product.single"})
	 * @JMS\Expose
	 */

	protected $content;

	/**
	 * Get content
	 * @return string
	 */
	public function getContent() {
		return $this->content;
	}

	/**
	 * Set content
	 * @return $this
	 */
	public function setContent($content) {
		$this->content = $content;
		return $this;
	}

	/**
	 * Get rating
	 * @return integer
	 */
	public function getRating() {
		return $this->rating;
	}

	/**
	 * Set rating
	 * @return $this
	 */
	public function setRating($rating) {
		$this->rating = $rating;
		return $this;
	}

}
