<?php declare(strict_types=1);

namespace App\EventListener;

use App\Exception\RedirectException;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class RedirectExceptionEventListener
{

	public function onKernelException(ExceptionEvent $event): void
	{
		$exception = $event->getThrowable();
		if ($exception instanceof RedirectException) {
			$event->setResponse($exception->getRedirectResponse());
		}
	}

}