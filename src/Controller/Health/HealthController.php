<?php declare(strict_types = 1);

namespace App\Controller\Health;

use Symfony\Component\HttpFoundation\JsonResponse;

class HealthController
{

	public function check(): JsonResponse
	{
		return new JsonResponse(['status' => 'ok']);
	}

}
