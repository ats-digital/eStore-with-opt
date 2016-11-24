<?php

namespace AppBundle\Service;

use AppBundle\Document\Product;
use AppBundle\Document\ProductList;
use AppBundle\Document\Review;
use AppBundle\Traits\DoctrineBatchUpdaterTrait;

class ProductImporterService {

	use DoctrineBatchUpdaterTrait;

	const STRATEGY_NATIVE = 'native';
	const STRATEGY_MANAGED = 'managed';

	const STRATEGY_RAW_JSON_DECODE = 'raw';
	const STRATEGY_JMS_SERIALIZER = 'jms';

	const DEFAULT_BATCH_SIZE = 5;

	private $dm;
	private $productApi;

	protected $importers;

	protected $batchSize;

	protected $serializer;

	protected $persistanceStrategy;
	protected $deserializationStrategy;

	protected $deserializationTime = 0;

	public function __construct($dm, $productApi, $serializer) {

		$this->dm = $dm;
		$this->productApi = $productApi;
		$this->batchSize = self::DEFAULT_BATCH_SIZE;
		$this->serializer = $serializer;
	}

	public function getImportStrategies() {

		if ($this->importers == null) {

			$this->importers = [];

			$this->importers[self::STRATEGY_MANAGED] = function ($stdProducts) {

				foreach ($stdProducts as $product) {

					switch ($this->getDeserializationStrategy()) {

					case self::STRATEGY_JMS_SERIALIZER:

						// $this->dm->persist($product);
						$this->getBatchUpdater()->synchronizeAndClear();

						break;

					case self::STRATEGY_RAW_JSON_DECODE:

						$managedProduct = new Product();

						$managedProduct
							->setImageUrl($product->imageUrl)
							->setProductMaterial($product->productMaterial)
							->setTag($product->tag)
							->setPrice($product->price)
							->setProductName($product->productName)
							->setColor($product->color)
							->setDescription($product->description)
							->setDetails($product->details)
						;

						foreach ($product->reviews as $review) {

							$managedReview = new Review();

							$managedReview
								->setRating($review->rating)
								->setContent($review->content)
							;

							$managedProduct->addReview($managedReview);
						}

						$this->dm->persist($managedProduct);

						break;

					default:
						break;
					}

				}

				$this->getDocumentManager()->flush();
			};

			$this->importers[self::STRATEGY_NATIVE] = function ($stdProducts) {

				$mongo = new \Mongo();

				$collection = $mongo->{$this->dm->getDocumentDatabase(Product::class)->getName()}->Product;

				if ($this->getDeserializationStrategy() == self::STRATEGY_JMS_SERIALIZER) {

					$stdProducts = array_map(function (Product $product) {

						return $product->toStdClass();

					}, $stdProducts);

				}

				$collection->batchInsert($stdProducts);

			};
		}

		return $this->importers;
	}

	public function getProducts() {

		$url = sprintf('/products?size=%s', $this->batchSize);

		$result = $this->deserialize($this->productApi->get($url)->getBody(), $this->getDeserializationStrategy());

		return $result;
	}

	public function getDeserializers() {

		$deserializers = [];

		$deserializers[self::STRATEGY_RAW_JSON_DECODE] = function ($output) {
			return json_decode($output)->products;
		};

		$deserializers[self::STRATEGY_JMS_SERIALIZER] = function ($output) {
			return $this->serializer->deserialize((string) $output, ProductList::class, 'json')->getProducts()->toArray();
		};

		return $deserializers;

	}

	public function deserialize($output, $strategy = self::STRATEGY_RAW_JSON_DECODE) {

		$start = microtime(true);

		$deserializationHandler = $this->getDeserializers()[$strategy];

		$deserializedOutput = call_user_func($deserializationHandler, $output);

		$end = microtime(true);

		$this->setDeserializationTime($end - $start);

		return $deserializedOutput;
	}

	public function import($strategy = self::STRATEGY_MANAGED) {

		$products = $this->getProducts($strategy);

		$start = microtime(true);

		$importHandler = $this->getImportStrategies()[$strategy];

		call_user_func($importHandler, $products);

		$end = microtime(true);

		return $end - $start;

	}

	public function ensureValidPersistanceStrategy($strategy) {

		if (!$strategy || $strategy == '') {
			$strategy == self::STRATEGY_MANAGED;
		}

		if (!in_array($strategy, [self::STRATEGY_MANAGED, self::STRATEGY_NATIVE])) {
			throw new \Exception(sprintf("Invalid Persistance Strategy %s", $strategy));
		}

		$this->setPersistanceStrategy($strategy);

		return true;
	}

	public function ensureValidDeserializationStrategy($strategy) {

		if (!$strategy || $strategy == '') {
			$strategy == self::STRATEGY_RAW_JSON_DECODE;
		}

		if (!in_array($strategy, [self::STRATEGY_JMS_SERIALIZER, self::STRATEGY_RAW_JSON_DECODE])) {
			throw new \Exception(sprintf("Invalid Deserialization Strategy %s", $strategy));
		}

		$this->setDeserializationStrategy($strategy);

		return true;
	}

	public function setBatchSize($batchSize) {
		$this->batchSize = $batchSize;
	}

	/**
	 * Get deserializationTime
	 * @return float
	 */
	public function getDeserializationTime() {
		return $this->deserializationTime;
	}

	/**
	 * Set deserializationTime
	 * @return $this
	 */
	public function setDeserializationTime($deserializationTime) {
		$this->deserializationTime = $deserializationTime;
		return $this;
	}

	/**
	 * Get persistanceStrategy
	 * @return string
	 */
	public function getPersistanceStrategy() {
		return $this->persistanceStrategy;
	}

	/**
	 * Set persistanceStrategy
	 * @return $this
	 */
	public function setPersistanceStrategy($persistanceStrategy) {
		$this->persistanceStrategy = $persistanceStrategy;
		return $this;
	}

	/**
	 * Get deserializationStrategy
	 * @return string
	 */
	public function getDeserializationStrategy() {
		return $this->deserializationStrategy;
	}

	/**
	 * Set deserializationStrategy
	 * @return $this
	 */
	public function setDeserializationStrategy($deserializationStrategy) {
		$this->deserializationStrategy = $deserializationStrategy;
		return $this;
	}

	public function getDocumentManager() {
		return $this->dm;
	}

	public function getBatchSize() {
		return 1000;
	}

}