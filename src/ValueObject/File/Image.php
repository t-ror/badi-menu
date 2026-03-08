<?php declare(strict_types = 1);

namespace App\ValueObject\File;

use InvalidArgumentException;
use Nette\Utils\Arrays;
use Nette\Utils\Strings;

class Image
{

	public const IMAGE_DIR = '/assets/img/db';
	/** Filesystem storage root, relative to the project root. Outside public/. */
	public const STORAGE_DIR = 'var/uploads/db';
	public const MIME_TYPE_JPEG = 'image/jpeg';
	public const MIME_TYPE_PNG = 'image/png';

	private string $className;
	private int $id;
	private string $fileName;

	/** @var array<int, string> */
	private array $allowedExtensions = [
		'jpg',
		'jpeg',
		'png',
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

	/**
	 * Relative path (class/id) used by ImageFacade to build the filesystem storage path.
	 * Does NOT include the storage root or the filename.
	 */
	public function getStorageRelativePath(): string
	{
		$className = (string) Arrays::last(explode('\\', $this->className));

		return Strings::webalize($className) . '/' . $this->id;
	}

	/**
	 * URL path served by ImageController. Security is enforced by security.yaml access_control.
	 */
	public function getWebPath(): string
	{
		$className = (string) Arrays::last(explode('\\', $this->className));

		return '/image/' . Strings::webalize($className) . '/' . $this->id . '/' . $this->fileName;
	}

	public function __toString(): string
	{
		return $this->getWebPath();
	}

	private function checkAllowedExtension(string $fileName): void
	{
		$extension = Strings::lower((string) Arrays::last(explode('.', $fileName)));

		if (!in_array($extension, $this->allowedExtensions, true)) {
			throw new InvalidArgumentException(sprintf('Invalid file extension "%s"', $extension));
		}
	}

}
