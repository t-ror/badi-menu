<?php declare(strict_types = 1);

namespace App\Entity\Collection;

use App\Entity\Meal;
use App\Entity\UserMeal;
use Doctrine\Common\Collections\ArrayCollection;

class UserMealCollection extends ArrayCollection
{

	/**
	 * @param array<UserMeal> $collection
	 */
	public function __construct(array $collection = [])
	{
		parent::__construct($collection);
	}

	public function hasMealAbleToPrepare(Meal $meal): bool
	{
		return $this->exists(function (int $key, UserMeal $userMeal) use ($meal): bool {
			return $userMeal->getMeal()->getId() === $meal->getId() && $userMeal->isAbleToPrepare();
		});
	}

	public function hasMealFavourite(Meal $meal): bool
	{
		return $this->exists(function (int $key, UserMeal $userMeal) use ($meal): bool {
			return $userMeal->getMeal()->getId() === $meal->getId() && $userMeal->isFavorite();
		});
	}

	public function getFavoriteUserMeals(): self
	{
		return new self($this->filter(function (UserMeal $userMeal): bool {
			return $userMeal->isFavorite();
		})->toArray());
	}

}
