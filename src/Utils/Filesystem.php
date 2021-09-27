<?php declare(strict_types = 1);

namespace App\Utils;

use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;

class Filesystem
{

	public static function remove(File $file): void
	{
		$fileSystem = new SymfonyFilesystem();

		$fileSystem->remove($file->getFilename());
	}

	public static function removeFiles(Finder $files): void
	{
		$fileSystem = new SymfonyFilesystem();

		$fileSystem->remove($files);
	}

	public static function exists(string $fileOrDirectory): bool
	{
		$fileSystem = new SymfonyFilesystem();

		return $fileSystem->exists($fileOrDirectory);
	}

}
