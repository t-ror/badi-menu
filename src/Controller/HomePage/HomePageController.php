<?php declare(strict_types = 1);

namespace App\Controller\HomePage;

use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\Response;

class HomePageController extends BaseController
{

	public function default(): Response {
		$this->checkAccessLoggedIn();
		$this->checkHouseholdSelected();

		return $this->renderByClass('default.html.twig');
	}

}