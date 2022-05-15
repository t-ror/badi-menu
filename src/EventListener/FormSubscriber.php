<?php declare(strict_types = 1);

namespace App\EventListener;

use App\Event\FormSubmittedEvent;
use App\Exception\RedirectException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FormSubscriber implements EventSubscriberInterface
{

	private UrlGeneratorInterface $urlGenerator;

	public function __construct(UrlGeneratorInterface $urlGenerator)
	{
		$this->urlGenerator = $urlGenerator;
	}

	/**
	 * @return array<string, mixed>
	 */
	public static function getSubscribedEvents(): array
	{
		return [FormSubmittedEvent::NAME_FILTER_FORM => 'onFilterFormSubmitted'];
	}

	public function onFilterFormSubmitted(FormSubmittedEvent $formSubmittedEvent): void
	{
		throw new RedirectException(
			new RedirectResponse(
				$this->urlGenerator->generate($formSubmittedEvent->getRedirectToAction(), $formSubmittedEvent->getValues())
			)
		);
	}

}
