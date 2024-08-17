<?php declare(strict_types = 1);

namespace App\Repository;

use App\Entity\Household;
use App\Entity\MealTag;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Nette\Utils\Arrays;

class MealTagRepository extends EntityRepository
{

	public function __construct(EntityManagerInterface $entityManager)
	{
		$classMetadata = $entityManager->getClassMetadata(MealTag::class);
		parent::__construct($entityManager, $classMetadata);
	}

	/**
	 * @return array<int, string>
	 */
	public function findPairs(?Household $household = null): array
	{
		$queryBuilder = $this->createQueryBuilder('mealTag')
			->addSelect('mealTag.id')
			->addSelect('mealTag.name');

		if ($household !== null) {
			$queryBuilder->andWhere('mealTag.household = :houseHold')
				->setParameter('houseHold', $household);
		}

		$mealTags = $queryBuilder->getQuery()->getArrayResult();

		/** @var array<int, string> $pairs */
		$pairs = Arrays::associate($mealTags, 'name=id');

		return $pairs;
	}

}
