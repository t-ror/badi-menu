<?php declare(strict_types = 1);

namespace App\Controller\Household;

use App\Component\Household\HouseholdList\HouseholdListFactory;
use App\Component\Household\UserHouseholdList\UserHouseholdListFactory;
use App\Controller\BaseController;
use App\Entity\Household;
use App\Repository\HouseholdRepository;
use App\Service\Household\HouseholdManager;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class HouseholdController extends BaseController
{

	public function __construct(
		private UserHouseholdListFactory $userHouseholdListFactory,
		private HouseholdManager $householdManager,
		private EntityManagerInterface $entityManager,
		private HouseholdListFactory $householdListFactory,
		private HouseholdRepository $householdRepository,
	)
	{
	}

	public function list(): Response
	{
		$user = $this->getLoggedInUser();
		$this->setActiveMenuLink(self::MENU_HOUSEHOLD);

		return $this->renderByClass('list.html.twig', [
			'userHouseholdList' => $this->userHouseholdListFactory->create($user)->render(),
		]);
	}

	public function select(int $id): Response
	{
		$user = $this->getLoggedInUser();
		$this->setActiveMenuLink(self::MENU_HOUSEHOLD);

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

	public function listAdd(): Response
	{
		$this->setActiveMenuLink(self::MENU_HOUSEHOLD);

		$user = $this->getLoggedInUser();
		$availableHouseholds = $this->householdRepository->findUnassignedForUser($user);

		return $this->renderByClass('listAdd.html.twig', [
			'householdList' => $this->householdListFactory->create($availableHouseholds)->render(),
		]);
	}

	public function add(int $id): Response
	{
		$user = $this->getLoggedInUser();
		$this->setActiveMenuLink(self::MENU_HOUSEHOLD);

		$userHousehold = $user->getUserHouseholdWithHouseholdId($id);
		if ($userHousehold !== null) {
			return $this->redirectToRoute('householdList');
		}

		/** @var Household|null $houseHold */
		$houseHold = $this->householdRepository->find($id);
		if ($houseHold === null) {
			return $this->redirectToRoute('householdListAdd');
		}

		$this->householdManager->addHouseholdToUser($houseHold, $user);

		$this->entityManager->flush();

		return $this->redirectToRoute('householdList');
	}

}
