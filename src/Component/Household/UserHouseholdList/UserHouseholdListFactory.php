<?php declare(strict_types = 1);

namespace App\Component\Household\UserHouseholdList;

use App\Entity\User;
use Twig\Environment;

class UserHouseholdListFactory
{

	private Environment $twig;

	public function __construct(Environment $twig)
	{
		$this->twig = $twig;
	}

	public function create(User $user): UserHouseholdList
	{
		return new UserHouseholdList($user, $this->twig);
	}

}
