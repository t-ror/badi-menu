<?php declare(strict_types = 1);

namespace App\EventListener;

use App\Event\FormSubmittedEvent;
use App\Exception\RedirectException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FormSubscriber implements EventSubscriberInterface
{

	private UrlGeneratorInterface $urlGenerator;
	private FlashBagInterface $flashBag;

	public function __construct(UrlGeneratorInterface $urlGenerator, FlashBagInterface $flashBag)
	{
		$this->urlGenerator = $urlGenerator;
		$this->flashBag = $flashBag;
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
