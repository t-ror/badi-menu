<?php declare(strict_types = 1);

namespace App\Twig;

use App\Entity\EntityOrm;
use App\Entity\Household;
use App\Entity\Meal;
use App\Entity\User;
use App\Utils\Strings;
use App\ValueObject\File\Image;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class ImageExtension extends AbstractExtension
{

	private const DIR_NO_IMG = '/assets/img/no-img/';
	private const NO_IMG_DEFAULT = 'image.svg';

	/** @var array<string, string>  */
	private array $noImgFilenames = [
		Household::class => 'house-door.svg',
		Meal::class => 'meal-icon.svg',
		User::class => 'user-default-icon.svg',
	];

	/**
	 * @return array<int, TwigFilter>
	 */
	public function getFilters(): array
	{
		return [
			new TwigFilter('checkNoImg', [$this, 'checkNoImg']),
		];
	}

	public function checkNoImg(?Image $image, ?EntityOrm $entity = null): string
	{
		if ($image === null || Strings::isEmpty($image->getFullFilename())) {
			$filename = $entity !== null && array_key_exists($this->getEntityClassName($entity), $this->noImgFilenames)
				? $this->noImgFilenames[$this->getEntityClassName($entity)]
				: self::NO_IMG_DEFAULT;

			return self::DIR_NO_IMG . $filename;
		}

		return $image->getFullFilename();
	}

	private function getEntityClassName(EntityOrm $entity): string
	{
		$nameParsed = explode('\\', get_class($entity));

		$name = [];
		foreach (array_reverse($nameParsed) as $value) {
			if ($value === '__CG__') {
				break;
			}

			$name[] = $value;
		}

		return implode('\\', array_reverse($name));
	}

}
