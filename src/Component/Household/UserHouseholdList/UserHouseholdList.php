<?php declare(strict_types = 1);

namespace App\Component\Household\UserHouseholdList;

use App\Component\Component;
use App\Entity\User;
use App\Service\Household\HouseholdManager;
use Twig\Environment;

class UserHouseholdList extends Component
{

	private User $user;
	private Environment $twig;
	private HouseholdManager $householdManager;

	public function __construct(User $user, Environment $twig, HouseholdManager $householdManager)
	{
		$this->user = $user;
		$this->twig = $twig;
		$this->householdManager = $householdManager;
	}

	public function render(): string
	{
		return $this->twig->render($this->getTemplatePath('userHouseholdList.html.twig'), [
			'userHouseholds' => $this->user->getUserHouseholdsOrdered(),
			'selectedHousehold' => $this->householdManager->getSelectedHouseholdForUserOrNull($this->user),
		]);
	}

	public function renderPreview(): string
	{
		return $this->twig->render($this->getTemplatePath('userHouseholdListPreview.html.twig'), [
			'userHouseholds' => $this->user->getUserHouseholdsOrdered(),
		]);
	}

}
