<?php declare(strict_types = 1);

namespace App\Component\Household\UserHouseholdList;

use App\Entity\User;
use App\Service\Household\HouseholdManager;
use Twig\Environment;

class UserHouseholdListFactory
{

	public function __construct(
		private Environment $twig,
		private HouseholdManager $householdManager,
	)
	{
	}

	public function create(User $user): UserHouseholdList
	{
		return new UserHouseholdList($user, $this->twig, $this->householdManager);
	}

}
