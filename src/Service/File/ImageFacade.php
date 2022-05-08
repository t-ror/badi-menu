<?php declare(strict_types = 1);

namespace App\Service\File;

use App\Utils\Filesystem;
use App\ValueObject\File\Image;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;

class ImageFacade
{

	private string $projectRootDir;

	public function __construct(string $projectRootDir)
	{
		$this->projectRootDir = $projectRootDir;
	}

	public function saveAndOverwrite(File $file, string $className, int $id, string $fileName): Image
	{
		$image = new Image($className, $id, $fileName);
		$this->removeFilesInDirectory($image->getDirectory());
		$file->move($this->projectRootDir . '/' . $image->getDirectory(), $fileName);

		return $image;
	}

	private function removeFilesInDirectory(string $directory): void
	{
		if (!Filesystem::exists($this->projectRootDir . '/' . $directory)) {
			return;
		}

		$fileFinder = new Finder();
		$fileFinder->files()->in($this->projectRootDir . '/' . $directory);

		Filesystem::removeFiles($fileFinder);
	}

}
