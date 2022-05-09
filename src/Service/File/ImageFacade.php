<?php declare(strict_types = 1);

namespace App\Service\File;

use App\Utils\Filesystem;
use App\ValueObject\File\Image;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;

class ImageFacade
{

	private string $publicAbsolutePath;

	public function __construct(string $projectRootDir)
	{
		$this->publicAbsolutePath = $projectRootDir . '/public';
	}

	public function saveAndOverwrite(File $file, string $className, int $id, string $fileName): Image
	{
		$image = new Image($className, $id, $fileName);
		$this->removeFilesInDirectory($image->getDirectory());
		$file->move($this->publicAbsolutePath . $image->getDirectory(), $fileName);

		return $image;
	}

	private function removeFilesInDirectory(string $directory): void
	{
		if (!Filesystem::exists($this->publicAbsolutePath . $directory)) {
			return;
		}

		$fileFinder = new Finder();
		$fileFinder->files()->in($this->publicAbsolutePath . $directory);

		Filesystem::removeFiles($fileFinder);
	}

}
