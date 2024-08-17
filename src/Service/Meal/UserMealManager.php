<?php declare(strict_types = 1);

namespace App\Service\Meal;

use App\Entity\Meal;
use App\Entity\User;
use App\Entity\UserMeal;
use Doctrine\ORM\EntityManagerInterface;

class UserMealManager
{

	public function __construct(private EntityManagerInterface $entityManager)
	{
	}

	public function addAbleToPrepare(User $user, Meal $meal): void
	{
		$userMeal = $user->getUserMealByMeal($meal);
		if ($userMeal !== null) {
			$userMeal->setAbleToPrepare(true);

			return;
		}

		$userMeal = new UserMeal($user, $meal);
		$userMeal->setAbleToPrepare(true);

		$this->entityManager->persist($userMeal);
	}

	public function addFavorite(User $user, Meal $meal): void
	{
		$userMeal = $user->getUserMealByMeal($meal);
		if ($userMeal !== null) {
			$userMeal->setFavorite(true);

			return;
		}

		$userMeal = new UserMeal($user, $meal);
		$userMeal->setFavorite(true);

		$this->entityManager->persist($userMeal);
	}

	public function removeAbleToPrepare(User $user, Meal $meal): void
	{
		$userMeal = $user->getUserMealByMeal($meal);
		if ($userMeal !== null) {
			$userMeal->setAbleToPrepare(false);

			return;
		}

		$userMeal = new UserMeal($user, $meal);
		$userMeal->setAbleToPrepare(false);

		$this->entityManager->persist($userMeal);
	}

	public function removeFavorite(User $user, Meal $meal): void
	{
		$userMeal = $user->getUserMealByMeal($meal);
		if ($userMeal !== null) {
			$userMeal->setFavorite(false);

			return;
		}

		$userMeal = new UserMeal($user, $meal);
		$userMeal->setFavorite(false);

		$this->entityManager->persist($userMeal);
	}

	public function toggleFavorite(User $user, Meal $meal): void
	{
		if ($user->isMealFavourite($meal)) {
			$this->removeFavorite($user, $meal);
		} else {
			$this->addFavorite($user, $meal);
		}
	}

	public function toggleAbleToPrepare(User $user, Meal $meal): void
	{
		if ($user->isAbleToPrepareMeal($meal)) {
			$this->removeAbleToPrepare($user, $meal);
		} else {
			$this->addAbleToPrepare($user, $meal);
		}
	}

}
