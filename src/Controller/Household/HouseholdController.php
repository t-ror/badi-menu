<?php declare(strict_types = 1);

namespace App\Controller\Household;

use App\Component\Household\UserHouseholdList\UserHouseholdListFactory;
use App\Controller\BaseController;
use App\Service\Household\HouseholdManager;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class HouseholdController extends BaseController
{

	private UserHouseholdListFactory $householdListFactory;
	private HouseholdManager $householdManager;
	private EntityManagerInterface $entityManager;

	public function __construct(
		UserHouseholdListFactory $householdListFactory,
		HouseholdManager $householdManager,
		EntityManagerInterface $entityManager
	)
	{
		$this->householdListFactory = $householdListFactory;
		$this->householdManager = $householdManager;
		$this->entityManager = $entityManager;
	}

	public function list(): Response
	{
		$this->checkAccessLoggedIn();
		$user = $this->getUserManager()->getLoggedUser();

		return $this->renderByClass('list.html.twig', [
			'userHouseholdList' => $this->householdListFactory->create($user)->render(),
		]);
	}

	public function select(int $id): Response
	{
		$this->checkAccessLoggedIn();
		$user = $this->getUserManager()->getLoggedUser();

		$userHousehold = $user->getUserHouseholdWithHouseholdId($id);
		if ($userHousehold === null) {
			return $this->redirectToRoute('householdList');
		}

		if (!$userHousehold->isAllowed()) {
			$this->addFlash('warning', 'Do domácnosti ještě nemáte přístup');

			return $this->redirectToRoute('householdList');
		}

		$this->householdManager->setSelectedHousehold($userHousehold->getHousehold(), $user);
		$userHousehold->setDateLastSelected(new DateTimeImmutable());
		$this->entityManager->flush();

		return $this->redirectToRoute('homepage');
	}

}
