<?php declare(strict_types=1);

namespace App\ValueObject\File;

use App\Entity\Entity;
use InvalidArgumentException;
use Nette\Utils\Arrays;
use Nette\Utils\Strings;

class Image
{

	public const IMAGE_DIR = DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'db';

	private Entity $entity;
	private string $fileName;

	/** @var array<int, string> */
	private array $allowedExtensions = [
		'jpg',
		'jpeg',
		'png',
		'svg',
	];

	public function __construct(Entity $entity, string $fileName)
	{
		$this->checkAllowedExtension($fileName);

		$this->entity = $entity;
		$this->fileName = $fileName;
	}

	public function getFileName(): string
	{
		return $this->fileName;
	}

	public function getFullFilename(): string
	{
		return $this->getDirectory() . DIRECTORY_SEPARATOR . $this->fileName;
	}

	public function getDirectory(): string
	{
		$fullClassName = get_class($this->entity);
		$className = (string) Arrays::last(explode('\\', $fullClassName));

		return implode(DIRECTORY_SEPARATOR, [
			self::IMAGE_DIR,
			Strings::webalize($className),
			$this->entity->getId()
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