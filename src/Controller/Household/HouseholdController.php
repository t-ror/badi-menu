<?php declare(strict_types = 1);

namespace App\Controller\Household;

use App\Component\Household\UserHouseholdList\UserHouseholdListFactory;
use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\Response;

class HouseholdController extends BaseController
{

	private UserHouseholdListFactory $householdListFactory;

	public function __construct(UserHouseholdListFactory $householdListFactory)
	{
		$this->householdListFactory = $householdListFactory;
	}

	public function list(): Response
	{
		$this->checkAccessLoggedIn();
		$user = $this->getUserManager()->getLoggedUser();

		return $this->renderByClass('list.html.twig', [
			'userHouseholdList' => $this->householdListFactory->create($user)->render(),
		]);
	}

}
