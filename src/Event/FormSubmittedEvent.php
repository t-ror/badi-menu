<?php declare(strict_types = 1);

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class FormSubmittedEvent extends Event
{

	public const NAME_DEFAULT = 'form.form_submitted';

	/** @var array<array<string, string>>  */
	private array $flashes = [];

	/**
	 * @param array<int|string, mixed> $values
	 */
	public function __construct(
		private string $redirectToAction,
		private array $values = [],
	)
	{
	}

	public function getRedirectToAction(): string
	{
		return $this->redirectToAction;
	}

	/**
	 * @return array<int|string, mixed>
	 */
	public function getValues(): array
	{
		return $this->values;
	}

	/**
	 * @return array<array<string, string>>
	 */
	public function getFlashes(): array
	{
		return $this->flashes;
	}

	public function addFlash(string $type, string $message): void
	{
		$this->flashes[] = [
			'type' => $type,
			'message' => $message,
		];
	}

}
