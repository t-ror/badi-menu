<?php declare(strict_types = 1);

namespace App\EventListener;

use App\Event\FormSubmittedEvent;
use App\Exception\RedirectException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FormSubscriber implements EventSubscriberInterface
{

	private FlashBagInterface $flashBag;

	public function __construct(
		private UrlGeneratorInterface $urlGenerator,
		RequestStack $requestStack
	)
	{
		/** @var Session $session */
		$session = $requestStack->getSession();
		$this->flashBag = $session->getFlashBag();
	}

	/**
	 * @return array<string, mixed>
	 */
	public static function getSubscribedEvents(): array
	{
		return [FormSubmittedEvent::NAME_DEFAULT => 'onFormSubmitted'];
	}

	public function onFormSubmitted(FormSubmittedEvent $formSubmittedEvent): void
	{
		foreach ($formSubmittedEvent->getFlashes() as $flash) {
			$this->flashBag->add($flash['type'], $flash['message']);
		}

		throw new RedirectException(
			new RedirectResponse(
				$this->urlGenerator->generate($formSubmittedEvent->getRedirectToAction(), $formSubmittedEvent->getValues())
			)
		);
	}

}
