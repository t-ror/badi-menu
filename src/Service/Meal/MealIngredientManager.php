<?php declare(strict_types = 1);

namespace App\Service\Meal;

use App\Entity\Ingredient;
use App\Entity\Meal;
use App\Entity\MealIngredient;
use Doctrine\ORM\EntityManagerInterface;

class MealIngredientManager
{

	public function __construct(private EntityManagerInterface $entityManager)
	{
	}

	public function addIngredientToMealByName(Meal $meal, string $ingredientName, ?string $amount = null): void
	{
		if ($meal->containsIngredientWithName($ingredientName)) {
			return;
		}

		$ingredient = $this->entityManager->getRepository(Ingredient::class)->findOneBy([
			'name' => $ingredientName,
		]);

		if ($ingredient === null) {
			$ingredient = new Ingredient($ingredientName);
			$this->entityManager->persist($ingredient);
		}

		$mealIngredient = new MealIngredient($meal, $ingredient);
		$mealIngredient->setAmount($amount);
		$this->entityManager->persist($mealIngredient);

		$meal->addMealIngredient($mealIngredient);
	}

}
