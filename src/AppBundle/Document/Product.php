<?php

namespace AppBundle\Document;

use AppBundle\Document\Product;
use AppBundle\Document\Review;
use AppBundle\Document\StdClassNormalizer;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;
use JMS\Serializer\Annotation\VirtualProperty;

/**
 * @ODM\Document(collection="Product",repositoryClass="AppBundle\Repository\ProductRepository")
 * @JMS\ExclusionPolicy("all")
 */

class Product extends StdClassNormalizer {

	function __construct() {
		$this->reviews = new ArrayCollection();
	}

	/**
	 * @ODM\Id(strategy="auto")
	 * @JMS\Type("string")
	 * @JMS\Groups({"product.all", "product.single"})
	 * @JMS\Expose
	 */
	protected $id;

	/**
	 * @ODM\String
	 * @JMS\Type("string")
	 * @JMS\Groups({"product.single"})
	 * @JMS\Expose
	 */
	protected $color;

	/**
	 * @ODM\String
	 * @JMS\Type("string")
	 * @JMS\Groups({"product.all", "product.single"})
	 * @JMS\SerializedName("productName")
	 * @JMS\Expose
	 */
	protected $productName;

	/**
	 * @ODM\Float
	 * @JMS\Type("double")
	 * @JMS\Groups({"product.all", "product.single"})
	 * @JMS\Expose
	 */
	protected $price;

	/**
	 * @ODM\String
	 * @JMS\Type("string")
	 * @JMS\Groups({"product.single", "product.all"})
	 * @JMS\Expose
	 */
	protected $tag;

	/**
	 * @ODM\String
	 * @JMS\Type("string")
	 * @JMS\Groups({"product.single"})
	 * @JMS\SerializedName("productMaterial")
	 * @JMS\Expose
	 */
	protected $productMaterial;

	/**
	 * @ODM\String
	 * @JMS\Type("string")
	 * @JMS\Groups({"product.single", "product.all"})
	 * @JMS\SerializedName("imageUrl")
	 * @JMS\Expose
	 */
	protected $imageUrl;

	/**
	 * @ODM\String
	 * @JMS\Type("string")
	 * @JMS\Groups({"product.single", "product.all"})
	 * @JMS\Expose
	 */
	protected $description;

	/**
	 * @ODM\String
	 * @JMS\Type("string")
	 * @JMS\Groups({"product.single"})
	 * @JMS\Expose
	 */
	protected $details;

	/**
	 * @ODM\EmbedMany(targetDocument="Review")
	 * @JMS\Type("ArrayCollection<AppBundle\Document\Review>")
	 * @JMS\SerializedName("reviews")
	 * @JMS\Groups({"product.single"})
	 * @JMS\Expose
	 */

	protected $reviews;

	/**
	 * Get imageUrl
	 * @return
	 */
	public function getImageUrl() {
		return $this->imageUrl;
	}

	/**
	 * @JMS\VirtualProperty
	 * @JMS\Groups({"product.all", "product.single"})
	 * Get imageUrl
	 * @return
	 */

	public function getImageUrlWithSuffix() {
		return $this->imageUrl ?
		sprintf('%s/%s', $this->imageUrl, $this->productMaterial) :
		null;
	}

	/**
	 * @JMS\VirtualProperty
	 * @JMS\Groups({"product.all", "product.single"})
	 * Get imageUrl
	 * @return
	 */

	public function getReviewCount() {
		return $this->reviews->count();
	}

	/**
	 * @JMS\VirtualProperty
	 * @JMS\Groups({"product.all", "product.single"})
	 * Get imageUrl
	 * @return
	 */

	public function getOverallRating() {

		$ratingSum = 0;

		foreach ($this->getReviews() as $review) {
			$ratingSum += $review->getRating();
		}

		return $this->getReviewCount() ? ceil($ratingSum / $this->getReviewCount()) : 0;
	}

	/**
	 * Set imageUrl
	 * @return $this
	 */
	public function setImageUrl($imageUrl) {
		$this->imageUrl = $imageUrl;
		return $this;
	}

	/**
	 * Get productMaterial
	 * @return
	 */
	public function getProductMaterial() {
		return $this->productMaterial;
	}

	/**
	 * Set productMaterial
	 * @return $this
	 */
	public function setProductMaterial($productMaterial) {
		$this->productMaterial = $productMaterial;
		return $this;
	}

	/**
	 * Get tag
	 * @return
	 */
	public function getTag() {
		return $this->tag;
	}

	/**
	 * Set tag
	 * @return $this
	 */
	public function setTag($tag) {
		$this->tag = $tag;
		return $this;
	}

	/**
	 * Get price
	 * @return
	 */
	public function getPrice() {
		return $this->price;
	}

	/**
	 * Set price
	 * @return $this
	 */
	public function setPrice($price) {
		$this->price = $price;
		return $this;
	}

	/**
	 * Get productName
	 * @return
	 */
	public function getProductName() {
		return $this->productName;
	}

	/**
	 * Set productName
	 * @return $this
	 */
	public function setProductName($productName) {
		$this->productName = $productName;
		return $this;
	}

	/**
	 * Get color
	 * @return string
	 */
	public function getColor() {
		return $this->color;
	}

	/**
	 * Set color
	 * @return $this
	 */
	public function setColor($color) {
		$this->color = $color;
		return $this;
	}

	/**
	 * Get description
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * Set description
	 * @return $this
	 */
	public function setDescription($description) {
		$this->description = $description;
		return $this;
	}

	/**
	 * Get details
	 * @return string
	 */
	public function getDetails() {
		return $this->details;
	}

	/**
	 * Set details
	 * @return $this
	 */
	public function setDetails($details) {
		$this->details = $details;
		return $this;
	}

	/**
	 * Add review
	 *
	 * @param \MInside\InsideBoard\CoreBundle\Document\Review $review
	 */
	public function addReview(Review $review) {
		if (!$this->reviews->contains($review)) {
			$this->reviews[] = $review;
		}
	}

	/**
	 * Remove review
	 *
	 * @param \MInside\InsideBoard\CoreBundle\Document\Review $review
	 */
	public function removeReview(Review $review) {
		$this->reviews->removeElement($review);
	}

	/**
	 * Get reviews
	 *
	 * @return \Doctrine\Common\Collections\Collection $reviews
	 */
	public function getReviews() {
		return $this->reviews;
	}

}
