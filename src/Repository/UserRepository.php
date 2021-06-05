<?php declare(strict_types = 1);

namespace App\Repository;

use App\Entity\User;
use Doctrine\ORM\EntityRepository;

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

}