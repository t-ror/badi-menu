<?php declare(strict_types = 1);

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class FormSubmittedEvent extends Event
{

	public const NAME_FILTER_FORM = 'form.filter_form_submitted';

	/** @var array<int|string, mixed> */
	private array $values;
	private string $redirectToAction;

	/**
	 * @param array<int|string, mixed> $values
	 */
	public function __construct(array $values, string $redirectToAction)
	{
		$this->values = $values;
		$this->redirectToAction = $redirectToAction;
	}

	/**
	 * @return array<int|string, mixed>
	 */
	public function getValues(): array
	{
		return $this->values;
	}

	public function getRedirectToAction(): string
	{
		return $this->redirectToAction;
	}

}
