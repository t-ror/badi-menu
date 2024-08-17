<?php declare(strict_types = 1);

namespace App\Entity;

use App\Entity\Traits\TId;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Entity]
#[Table(name: 'app_meal_ingredient')]
class MealIngredient extends EntityOrm
{

	use TId;

	#[ManyToOne(targetEntity: Meal::class, inversedBy: 'mealIngredients')]
	#[JoinColumn(referencedColumnName: 'id', nullable: false)]
	private Meal $meal;

	#[ManyToOne(targetEntity: Ingredient::class)]
	#[JoinColumn(referencedColumnName: 'id', nullable: false)]
	private Ingredient $ingredient;

	#[Column(type: 'string', length: 32, nullable: true)]
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
