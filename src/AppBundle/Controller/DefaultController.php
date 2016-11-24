<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller {

	public function indexAction(Request $request) {
		return $this->render('AppBundle:Home:base.html.twig');
	}

	public function indexNoSpaAction(Request $request) {

		$products = $this->getDocumentManager()
			->getRepository('AppBundle:Product')
			->findBy([], [], 50, 0);

		return $this->render('AppBundle:Home:base_no_spa.html.twig', ['products' => $products]);
	}

	public function getDocumentManager() {
		return $this->get('doctrine.odm.mongodb.document_manager');
	}

}
