<?php declare(strict_types = 1);

namespace App\Repository;

use App\Entity\Household;
use App\Entity\User;
use App\Entity\UserHousehold;
use App\Utils\UserUrl;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Nette\Utils\Arrays;
use Override;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserRepository extends EntityRepository implements UserLoaderInterface
{

	public function __construct(EntityManagerInterface $entityManager)
	{
		$classMetadata = $entityManager->getClassMetadata(User::class);
		parent::__construct($entityManager, $classMetadata);
	}

	#[Override]
	public function loadUserByIdentifier(string $identifier): ?UserInterface
	{
		return $this->getByNameOrEmail($identifier);
	}

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

	public function getByUserUrl(UserUrl $userUrl): ?User
	{
		return $this->createQueryBuilder('users')
			->where('users.id = :userId')
			->andWhere('users.name = :name')
			->setParameter('userId', $userUrl->getId())
			->setParameter('name', $userUrl->getName())
			->getQuery()
			->getOneOrNullResult();
	}

	/**
	 * @return array<int, string>
	 */
	public function findPairs(?Household $household = null, ?User $currenUser = null): array
	{
		if ($currenUser !== null) {
			$queryBuilder = $this->createQueryBuilder('users')
				->addSelect('users.id')
				->addSelect('CASE WHEN users = :currentUser THEN CONCAT(users.name, \' (Já)\') ELSE users.name END AS name')
				->addSelect('CASE WHEN users = :currentUser THEN 1 ELSE 0 END AS HIDDEN orderCurrentUser')
				->setParameter('currentUser', $currenUser)
				->orderBy('orderCurrentUser', 'DESC');
		} else {
			$queryBuilder = $this->createQueryBuilder('users')
				->addSelect('users.id')
				->addSelect('users.name');
		}

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

		$userMeals = $queryBuilder->getQuery()->getArrayResult();

		/** @var array<int, string> $pairs */
		$pairs = Arrays::associate($userMeals, 'name=id');

		return $pairs;
	}

}
