<?php declare(strict_types = 1);

namespace App\Service\Household;

use App\Entity\Household;
use App\Entity\User;
use App\Entity\UserHousehold;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class HouseholdManager
{

	private const GLOBAL_SELECTED_HOUSEHOLD = 'selectedHousehold';
	private const GLOBAL_USER_NAME = 'user';
	private const GLOBAL_HOUSEHOLD_ID = 'household';

	private SessionInterface $session;
	private EntityManagerInterface $entityManager;

	public function __construct(RequestStack $requestStack, EntityManagerInterface $entityManager)
	{
		$this->session = $requestStack->getSession();
		$this->entityManager = $entityManager;
	}

	public function setSelectedHousehold(Household $household, User $user): void
	{
		$this->session->set(
			self::GLOBAL_SELECTED_HOUSEHOLD,
			[self::GLOBAL_USER_NAME => $user->getName(), self::GLOBAL_HOUSEHOLD_ID => $household->getId()]
		);

		$this->session->save();
	}

	public function getSelectedHouseholdForUserOrNull(User $user): ?Household
	{
		$selectedHousehold = $this->session->get(self::GLOBAL_SELECTED_HOUSEHOLD);
		if ($selectedHousehold === null) {
			return null;
		}

		$username = array_key_exists(self::GLOBAL_USER_NAME, $selectedHousehold)
			? $selectedHousehold[self::GLOBAL_USER_NAME]
			: null;

		if ($username === null || $username !== $user->getName()) {
			return null;
		}

		$householdId = array_key_exists(self::GLOBAL_HOUSEHOLD_ID, $selectedHousehold)
			? $selectedHousehold[self::GLOBAL_HOUSEHOLD_ID]
			: null;

		if ($householdId === null) {
			return null;
		}

		return $this->entityManager->find(Household::class, $householdId);
	}

	public function getSelectedHouseholdForUser(User $user): Household
	{
		$household = $this->getSelectedHouseholdForUserOrNull($user);
		if ($household === null) {
			throw new InvalidArgumentException('Household is not selected');
		}

		return $household;
	}

	public function addHouseholdToUser(Household $household, User $user): void
	{
		$allowed = !$household->hasAnyMember();

		$userHousehold = new UserHousehold($user, $household);
		$userHousehold->setAllowed($allowed);

		$this->entityManager->persist($userHousehold);
	}

}
