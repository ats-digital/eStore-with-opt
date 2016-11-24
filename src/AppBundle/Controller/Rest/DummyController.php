<?php

namespace AppBundle\Controller\Rest;

use AppBundle\Annotation\Cacheable;
use AppBundle\Annotation\Profileable;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use JMS\Serializer\SerializationContext;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Stopwatch\Stopwatch;

class DummyController extends FOSRestController {

	public function getDocumentManager() {
		return $this->get('doctrine_mongodb.odm.document_manager');
	}

	/**
	 * @QueryParam(name="forceMiss", nullable=true, requirements="\d+")
	 * @param ParamFetcher $paramFetcher
	 * @return JsonResponse
	 * @Profileable(profileId="getTagsAction")
	 * @Cacheable(key="someKeyToRemember")
	 */

	public function getTagsAction(ParamFetcher $paramFetcher) {

		$forceMiss = (int) $paramFetcher->get('forceMiss');

		$tags = $this->getFromCache('tags.all', function () {

			$result = $this->getDocumentManager()
				->getRepository('AppBundle:Product')
				->getAllTags()
			;

			array_unshift($result, 'All Tags');

			return $this->getJsonPayload($result);

		}, 1 || $forceMiss);

		return new Response($tags, Response::HTTP_OK);
	}

	/**
	 * @QueryParam(name="forceMiss", nullable=true, requirements="\d+")
	 * @param ParamFetcher $paramFetcher
	 * @return JsonResponse
	 * @Profileable(profileId="getProductsAction")
	 * @Cacheable(key="getProductionsAction")
	 */

	public function getProductsAction(ParamFetcher $paramFetcher) {

		$forceMiss = (int) $paramFetcher->get('forceMiss');

		$eventKey = 'products.all';

		$products = $this->getFromCache($eventKey, function () {
			return $this->getJsonPayload(
				$this->getDocumentManager()
					->getRepository('AppBundle:Product')
					->findBy([], [], 50),
				'product.all'

			);
		}, $forceMiss);

		return new Response($products, Response::HTTP_OK);

	}

	/**
	 *
	 * @QueryParam(name="forceMiss", nullable=true, requirements="\d+")
	 * @param ParamFetcher $paramFetcher
	 * @return JsonResponse
	 */

	public function getProductAction(ParamFetcher $paramFetcher, $id) {

		$forceMiss = (int) $paramFetcher->get('forceMiss');

		$stopwatch = new Stopwatch();

		$eventKey = sprintf('products.%s', $id);

		$stopwatch->start($eventKey);

		$product = $this->getFromCache($eventKey, function () use ($id) {

			return $this->getJsonPayload(
				$this->getDocumentManager()
					->getRepository('AppBundle:Product')
					->find($id),
				'product.single'
			);
		}, $forceMiss);

		$event = $stopwatch->stop($eventKey);

		return new Response($product, Response::HTTP_OK, ['X-Duration' => $event->getDuration()]);

	}

	public function getPersistanceDeserializationBatchImportAction($persistanceStrategy, $deserializationStrategy, $batchSize) {

		$importerCommand = $this->get('command.importer.products');

		$input = new ArrayInput(['--batch-size' => intval($batchSize), '--import-strategy' => $persistanceStrategy, '--deserialization-strategy' => $deserializationStrategy]);
		$output = new NullOutput();
		$importerCommand->run($input, $output);

		return new JsonResponse($importerCommand->getResult());
	}

	/**
	 *
	 * @return SerializerInterface
	 */
	protected function getSerializer() {

		static $result = null;

		if (null == $result) {
			$result = $this->get('jms_serializer');
		}
		return $result;
	}

	protected function getJsonPayload($payload, $serializationGroup = null) {

		return $serializationGroup ?

		$this->getSerializer()->serialize($payload, 'json', $this->getSerializationContext($serializationGroup)) :
		$this->getSerializer()->serialize($payload, 'json')
		;
	}

	public function getFromCache($key, \Closure $callback, $forceMiss = false) {

		if ($forceMiss) {
			return call_user_func($callback);
		}

		$cache = $this->getCacheProvider()->getCache();

		$cachedEntities = $cache->getItem($key);

		if (!$cachedEntities->isHit()) {

			$cachedEntities->set(call_user_func($callback));
			$cache->save($cachedEntities);
		}

		return $cachedEntities->get();
	}

	/**
	 *
	 * @param string $group
	 * @return SerializationContext
	 */
	protected function getSerializationContext($group) {

		$context = SerializationContext::create()->setGroups(array($group))->setSerializeNull(true);

		return $context;
	}

	protected function getCacheProvider() {
		return $this->get('cache.redis');
	}

}
