<?php declare(strict_types = 1);

namespace App\Component\Household\UserHouseholdList;

use App\Entity\User;
use App\Service\Household\HouseholdManager;
use Twig\Environment;

class UserHouseholdListFactory
{

	private Environment $twig;
	private HouseholdManager $householdManager;

	public function __construct(Environment $twig, HouseholdManager $householdManager)
	{
		$this->twig = $twig;
		$this->householdManager = $householdManager;
	}

	public function create(User $user): UserHouseholdList
	{
		return new UserHouseholdList($user, $this->twig, $this->householdManager);
	}

}
