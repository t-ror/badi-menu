<?php declare(strict_types = 1);

namespace App\Controller\Error;

use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ErrorController extends BaseController
{

	public function show(Request $request): Response
	{
		$exception = $request->get('exception');
		if (!$this->isProductionEnv()) {
			throw $exception;
		}

		if ($exception instanceof NotFoundHttpException) {
			return $this->renderByClass('error404.html.twig');
		}

		return $this->renderByClass('error.html.twig');
	}

}
