<?php declare(strict_types = 1);

namespace App\Service\File;

use App\Utils\Filesystem;
use App\ValueObject\File\Image;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;

class ImageFacade
{

	private string $storageRoot;

	public function __construct(string $projectRootDir)
	{
		$this->storageRoot = $projectRootDir . '/' . Image::STORAGE_DIR;
	}

	/**
	 * Saves the uploaded file under var/uploads/db/{class}/{id}/ using a cryptographically
	 * random filename derived from the validated MIME type. The client-supplied filename is
	 * never used. Returns the Image value object that should be persisted on the entity.
	 */
	public function saveAndOverwrite(File $file, string $className, int $id): Image
	{
		$extension = $file->guessExtension() ?? 'bin';
		$randomFileName = bin2hex(random_bytes(16)) . '.' . $extension;

		$image = new Image($className, $id, $randomFileName);
		$storageDir = $this->storageRoot . '/' . $image->getStorageRelativePath();

		$this->removeFilesInDirectory($storageDir);
		Filesystem::creatDirectory($storageDir);
		$file->move($storageDir, $randomFileName);

		return $image;
	}

	private function removeFilesInDirectory(string $absoluteDirectory): void
	{
		if (!Filesystem::exists($absoluteDirectory)) {
			return;
		}

		$fileFinder = new Finder();
		$fileFinder->files()->in($absoluteDirectory);

		Filesystem::removeFiles($fileFinder);
	}

}
