<?php declare(strict_types = 1);

namespace App\Exception;

use Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Throwable;

class RedirectException extends Exception
{

	public function __construct(
		private RedirectResponse $redirectResponse,
		string $message = '',
		int $code = 0,
		?Throwable $previousException = null
	)
	{
		parent::__construct($message, $code, $previousException);
	}

	public function getRedirectResponse(): RedirectResponse
	{
		return $this->redirectResponse;
	}

}
