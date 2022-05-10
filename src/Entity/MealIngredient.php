<?php declare(strict_types = 1);

namespace App\Entity;

use App\Entity\Traits\TId;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="app_meal_ingredient")
 */
class MealIngredient extends Entity
{

	use TId;

	/**
	 * @ORM\ManyToOne(targetEntity="Meal", inversedBy="mealIngredients")
	 * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
	 */
	private Meal $meal;

	/**
	 * @ORM\ManyToOne(targetEntity="Ingredient")
	 * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
	 */
	private Ingredient $ingredient;

	/** @ORM\Column(length=32, type="string", nullable=true) */
	private ?string $amount = null;

	public function __construct(Meal $meal, Ingredient $ingredient)
	{
		$this->meal = $meal;
		$this->ingredient = $ingredient;

		$meal->addMealIngredient($this);
	}

	public function getMeal(): Meal
	{
		return $this->meal;
	}

	public function getIngredient(): Ingredient
	{
		return $this->ingredient;
	}

	public function getAmount(): ?string
	{
		return $this->amount;
	}

	public function setAmount(?string $amount): void
	{
		$this->amount = $amount;
	}

}
