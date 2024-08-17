<?php declare(strict_types = 1);

namespace App\Entity;

use App\Entity\Traits\TId;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[Entity]
#[Table(name: 'app_household_meal')]
#[UniqueConstraint(columns: ['household_id', 'meal_id'])]
class HouseholdMeal extends EntityOrm
{

	use TId;

	#[ManyToOne(targetEntity: Household::class, inversedBy: 'householdMeals')]
	#[JoinColumn(referencedColumnName: 'id', nullable: false)]
	private Household $household;

	#[ManyToOne(targetEntity: Meal::class, inversedBy: 'householdMeals')]
	#[JoinColumn(referencedColumnName: 'id', nullable: false)]
	private Meal $meal;

	public function __construct(Household $household, Meal $meal)
	{
		$this->household = $household;
		$this->meal = $meal;

		$household->addHouseholdMeal($this);
		$meal->addHouseholdMeal($this);
	}

	public function getHousehold(): Household
	{
		return $this->household;
	}

	public function getMeal(): Meal
	{
		return $this->meal;
	}

}
