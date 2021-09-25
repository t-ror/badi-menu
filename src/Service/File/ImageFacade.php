<?php declare(strict_types=1);

namespace App\Service\File;

use App\Entity\Entity;
use App\Utils\Filesystem;
use App\ValueObject\File\Image;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;

class ImageFacade
{

	public function saveAndOverwrite(File $file, Entity $entity, string $fileName): void
	{
		$image = new Image($entity, $fileName);
		$this->removeFilesInDirectory($image->getDirectory());

		$file->move($image->getDirectory(), $fileName);
	}

	private function removeFilesInDirectory(string $directory): void
	{
		if (Filesystem::exists($directory)) {
			return;
		}

		$fileFinder = new Finder();
		$fileFinder->files()->in($directory);

		Filesystem::removeFiles($fileFinder);
	}


}