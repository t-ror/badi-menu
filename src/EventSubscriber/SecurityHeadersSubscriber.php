<?php

declare(strict_types = 1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class SecurityHeadersSubscriber implements EventSubscriberInterface
{

	/**
	 * @return array<string, string|array{0: string, 1: int}|list<array{0: string, 1?: int}>>
	 */
	public static function getSubscribedEvents(): array
	{
		return [KernelEvents::RESPONSE => 'onKernelResponse'];
	}

	public function onKernelResponse(ResponseEvent $event): void
	{
		if (!$event->isMainRequest()) {
			return;
		}

		$response = $event->getResponse();
		$headers = $response->headers;

		$headers->set('X-Content-Type-Options', 'nosniff');
		$headers->set('X-Frame-Options', 'SAMEORIGIN');
		$headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
		$headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
	}

}
