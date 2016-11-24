<?php

namespace AppBundle\Document;

use JMS\Serializer\Annotation as JMS;

class ProductList {

	/**
	 * @JMS\Type("ArrayCollection<AppBundle\Document\Product>")
	 * @JMS\SerializedName("products")
	 */

	protected $products;

	/**
	 * Get products
	 * @return Product
	 */
	public function getProducts() {
		return $this->products;
	}

	/**
	 * Set products
	 * @return $this
	 */
	public function setProducts($products) {
		$this->products = $products;
		return $this;
	}

}
