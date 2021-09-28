<?php declare(strict_types = 1);

namespace App\ValueObject\File;

use InvalidArgumentException;
use Nette\Utils\Arrays;
use Nette\Utils\Strings;

class Image
{

	public const IMAGE_DIR = '/assets/img/db';

	private string $className;
	private int $id;
	private string $fileName;

	/** @var array<int, string> */
	private array $allowedExtensions = [
		'jpg',
		'jpeg',
		'png',
		'svg',
	];

	public function __construct(string $className, int $id, string $fileName)
	{
		$this->checkAllowedExtension($fileName);

		$this->fileName = $fileName;
		$this->className = $className;
		$this->id = $id;
	}

	public function getFileName(): string
	{
		return $this->fileName;
	}

	public function getFullFilename(): string
	{
		return $this->getDirectory() . '/' . $this->fileName;
	}

	public function getDirectory(): string
	{
		$className = (string) Arrays::last(explode('\\', $this->className));

		return implode('/', [
			self::IMAGE_DIR,
			Strings::webalize($className),
			$this->id,
		]);
	}

	public function __toString(): string
	{
		return $this->getFullFilename();
	}

	private function checkAllowedExtension(string $fileName): void
	{
		$extension = Strings::lower((string) Arrays::last(explode('.', $fileName)));

		if (!in_array($extension, $this->allowedExtensions, true)) {
			throw new InvalidArgumentException(sprintf('Invalid file extension "%s"', $extension));
		}
	}

}
