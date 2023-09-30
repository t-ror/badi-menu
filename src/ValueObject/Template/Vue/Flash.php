<?php declare(strict_types = 1);

namespace App\ValueObject\Template\Vue;

class Flash
{

	private const TYPE_SUCCESS = 'success';
	private const TYPE_WARNING = 'warning';
	private const TYPE_DANGER = 'danger';

	private string $type;
	private string $message;

	public function __construct(string $type, string $message)
	{
		$this->type = $type;
		$this->message = $message;
	}

	public static function createSuccess(string $message): self
	{
		return new self(self::TYPE_SUCCESS, $message);
	}

	public static function createWarning(string $message): self
	{
		return new self(self::TYPE_WARNING, $message);
	}

	public static function createDanger(string $message): self
	{
		return new self(self::TYPE_DANGER, $message);
	}

	public function getType(): string
	{
		return $this->type;
	}

	public function getMessage(): string
	{
		return $this->message;
	}

}
