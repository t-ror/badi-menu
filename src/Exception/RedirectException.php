<?php declare(strict_types=1);

namespace App\Exception;

use Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;

class RedirectException extends Exception
{

	private RedirectResponse $redirectResponse;

	public function __construct(
		RedirectResponse $redirectResponse,
		string $message = '',
		int $code = 0,
		?Exception $previousException = null
	) {
		$this->redirectResponse = $redirectResponse;
		parent::__construct($message, $code, $previousException);
	}

	public function getRedirectResponse(): RedirectResponse
	{
		return $this->redirectResponse;
	}

}