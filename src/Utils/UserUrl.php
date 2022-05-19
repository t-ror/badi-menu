<?php declare(strict_types = 1);

namespace App\Utils;

use App\Entity\User;

class UserUrl
{

	private const SEPARATOR = '-';
	private const ORDER_NAME = 0;
	private const ORDER_ID = 1;

	private string $name;
	private string $id;

	public function __construct(string $name, string $id)
	{
		$this->name = $name;
		$this->id = $id;
	}

	public static function createFromUser(User $user): self
	{
		return new self($user->getName(), (string) $user->getId());
	}

	public static function createFromUrl(string $url): self
	{
		$parts = explode(self::SEPARATOR, $url);

		return new self($parts[self::ORDER_NAME], $parts[self::ORDER_ID]);
	}

	public function getUrl(): string
	{
		return sprintf(
			'%s%s%d',
			Strings::webalize($this->name),
			self::SEPARATOR,
			$this->id
		);
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function setName(string $name): void
	{
		$this->name = $name;
	}

	public function getId(): string
	{
		return $this->id;
	}

	public function setId(string $id): void
	{
		$this->id = $id;
	}

}
