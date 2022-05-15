<?php declare(strict_types = 1);

namespace App\Repository;

use App\Entity\Household;
use App\Entity\User;
use App\Entity\UserHousehold;
use Doctrine\ORM\EntityRepository;
use Nette\Utils\Arrays;

class UserRepository extends EntityRepository
{

	public function getByNameOrEmail(string $nameOrEmail): ?User
	{
		return $this->createQueryBuilder('users')
			->where('(users.name = :nameOrEmail OR users.email = :nameOrEmail)')
			->setParameter('nameOrEmail', $nameOrEmail)
			->getQuery()
			->getOneOrNullResult();
	}

	public function getByName(string $name): ?User
	{
		return $this->createQueryBuilder('users')
			->where('users.name = :name')
			->setParameter('name', $name)
			->getQuery()
			->getOneOrNullResult();
	}

	public function getByEmail(string $email): ?User
	{
		return $this->createQueryBuilder('users')
			->where('users.email = :email')
			->setParameter('email', $email)
			->getQuery()
			->getOneOrNullResult();
	}

	/**
	 * @return array<int, string>
	 */
	public function findPairs(?Household $household = null): array
	{
		$queryBuilder = $this->createQueryBuilder('users')
			->addSelect('users.id')
			->addSelect('users.name');

		if ($household !== null) {
			$expr = $this->getEntityManager()->getExpressionBuilder();
			$householdExists = $expr->exists(
				$this->getEntityManager()->createQueryBuilder()
					->select('1')
					->from(UserHousehold::class, 'userHousehold')
					->where('userHousehold.user = users')
					->andWhere('userHousehold.household = :household')
					->andWhere('userHousehold.allowed = 1')
			);

			$queryBuilder->andWhere($householdExists)
				->setParameter('household', $household);
		}

		$mealTags = $queryBuilder->getQuery()->getArrayResult();

		/** @var array<int, string> $pairs */
		$pairs = Arrays::associate($mealTags, 'name=id');

		return $pairs;
	}

}
