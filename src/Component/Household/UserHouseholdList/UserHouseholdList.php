<?php declare(strict_types = 1);

namespace App\Component\Household\UserHouseholdList;

use App\Component\Household\Component;
use App\Entity\User;
use Twig\Environment;

class UserHouseholdList extends Component
{

	private User $user;
	private Environment $twig;

	public function __construct(User $user, Environment $twig)
	{
		$this->user = $user;
		$this->twig = $twig;
	}

	public function render(): string
	{
		return $this->twig->render($this->getTemplatePath('userHouseholdList.html.twig'), [
			'userHouseholds' => $this->user->getUserHouseholdsOrdered(),
		]);
	}

}
