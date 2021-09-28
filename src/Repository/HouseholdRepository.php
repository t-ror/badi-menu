<?php declare(strict_types = 1);

namespace App\Repository;

use App\Entity\Household;
use App\Entity\User;
use App\Entity\UserHousehold;
use Doctrine\ORM\EntityRepository;

class HouseholdRepository extends EntityRepository
{

	/**
	 * @return array<int, Household>
	 */
	public function findUnassignedForUser(User $user): array
	{
		$entityManager = $this->getEntityManager();
		$expr = $entityManager->getExpressionBuilder();
		$userHouseholdNotExist = $expr->not(
			$expr->exists(
				$entityManager->createQueryBuilder()
					->select('1')
					->from(UserHousehold::class, 'userHousehold')
					->andWhere('userHousehold.household = household')
					->andWhere('userHousehold.user = :user')
			)
		);

		return $this->createQueryBuilder('household')
			->andWhere($userHouseholdNotExist)
			->setParameter('user', $user)
			->getQuery()
			->getResult();
	}

}
