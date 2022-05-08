<?php declare(strict_types = 1);

namespace App\Service\Meal;

use App\Entity\Ingredient;
use App\Entity\Meal;
use App\Entity\MealIngredient;
use Doctrine\ORM\EntityManagerInterface;

class MealIngredientManager
{

	private EntityManagerInterface $entityManager;

	public function __construct(EntityManagerInterface $entityManager)
	{
		$this->entityManager = $entityManager;
	}

	public function addIngredientToMealByName(Meal $meal, string $ingredientName, ?string $amount = null): void
	{
		$ingredient = $this->entityManager->getRepository(Ingredient::class)->findOneBy([
			'name' => $ingredientName,
		]);

		if (
            ($ingredient !== null && $meal->containsIngredient($ingredient))
            || $meal->containsIngredientWithName($ingredientName)
        ) {
			return;
		}

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
