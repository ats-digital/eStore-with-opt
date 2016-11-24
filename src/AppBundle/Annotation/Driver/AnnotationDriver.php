<?php

namespace AppBundle\Annotation\Driver;

use AppBundle\Annotation\Cacheable;
use AppBundle\Annotation\Profileable;
use AppBundle\Exception\RedisDispatchableException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Stopwatch\Stopwatch;

class AnnotationDriver {

	private $reader;
	private $stopwatch;

	public function __construct($reader, $stopwatchProvider, $cacheProvider) {

		$this->reader = $reader;
		$this->cache = $cacheProvider->getCache();
		$this->stopwatch = $stopwatchProvider->getStopwatcher();

	}

	public function onKernelController(FilterControllerEvent $event) {

		if (!is_array($controller = $event->getController())) {
			return;
		}

		$object = new \ReflectionObject($controller[0]);
		$method = $object->getMethod($controller[1]);

		$request = $event->getRequest();

		foreach ($this->reader->getMethodAnnotations($method) as $configuration) {

			if ($configuration instanceof Profileable) {

				$profileId = $configuration->getProfileId();

				$request->attributes->set('X-Profiler-Id', $profileId);

				$this->stopwatch->start($profileId);

			}

			if ($configuration instanceof Cacheable) {

				$cacheKey = $configuration->getKey();

				$cachedEntities = $this->cache->getItem($cacheKey);

				if (!$cachedEntities->isHit()) {

					$request->attributes->set('X-Cacheable-Key', $cacheKey);
					return;

				}

				$exceptionPayload = json_encode(['cacheKey' => $cacheKey]);

				throw new RedisDispatchableException($exceptionPayload);
			}

		}
	}

	public function onKernelException(GetResponseForExceptionEvent $event) {

		$exception = $event->getException();

		if (!($exception instanceof RedisDispatchableException)) {
			return;
		}

		$exceptionPayload = json_decode($exception->getMessage());

		$payload = $this->cache->getItem($exceptionPayload->cacheKey)->get();

		$response = new Response($payload, Response::HTTP_OK);
		$response->headers->set('X-Status-Code', 200);
		$response->headers->set('X-Cache-Hit', 1);

		$event->setResponse($response);
	}

	public function onKernelResponse(FilterResponseEvent $event) {

		$request = $event->getRequest();

		$profileId = $request->attributes->get('X-Profiler-Id');

		if ($profileId !== null) {
			$profileEvent = $this->stopwatch->stop($profileId);
			$event->getResponse()->headers->set('X-Profiler-Memory', $profileEvent->getMemory());
			$event->getResponse()->headers->set('X-Profiler-Duration', $profileEvent->getDuration());
		}

		$cacheKey = $request->attributes->get('X-Cacheable-Key'); // means request is @Cacheable

		if ($cacheKey !== null) {

			$cachedEntity = $this->cache->getItem($cacheKey);
			$cachedEntity->set($event->getResponse()->getContent());

			$this->cache->save($cachedEntity);

		}

	}

}