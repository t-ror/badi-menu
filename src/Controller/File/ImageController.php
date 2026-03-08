<?php declare(strict_types = 1);

namespace App\Controller\File;

use App\ValueObject\File\Image;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class ImageController extends AbstractController
{

	private string $storageRoot;

	public function __construct(string $projectRootDir)
	{
		$this->storageRoot = $projectRootDir . '/' . Image::STORAGE_DIR;
	}

	/**
	 * Serves uploaded images stored outside the web root.
	 * Access is controlled by security.yaml access_control — only authenticated users reach here.
	 *
	 * Route parameters are validated strictly to prevent path traversal:
	 *   {class}    — webalized entity class name (a-z, 0-9, hyphen only)
	 *   {id}       — positive integer entity ID
	 *   {filename} — 32 hex chars + allowed image extension
	 */
	public function serve(string $class, int $id, string $filename): Response
	{
		if (!preg_match('/^[a-z0-9\-]+$/', $class)) {
			throw $this->createNotFoundException();
		}

		if (!preg_match('/^[a-f0-9]{32}\.(jpg|jpeg|png)$/', $filename)) {
			throw $this->createNotFoundException();
		}

		$absolutePath = $this->storageRoot . '/' . $class . '/' . $id . '/' . $filename;

		if (!is_file($absolutePath)) {
			throw $this->createNotFoundException();
		}

		return new BinaryFileResponse($absolutePath);
	}

}