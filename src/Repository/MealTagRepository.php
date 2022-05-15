<?php declare(strict_types = 1);

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use Nette\Utils\Arrays;

class MealTagRepository extends EntityRepository
{

	/**
	 * @return array<int, string>
	 */
	public function findPairs(): array
	{
		$mealTags = $this->createQueryBuilder('mealTag')
			->addSelect('mealTag.id')
			->addSelect('mealTag.name')
			->getQuery()
			->getArrayResult();

		/** @var array<int, string> $pairs */
		$pairs = Arrays::associate($mealTags, 'name=id');

		return $pairs;
	}

}
