<?php declare(strict_types = 1);

namespace App\Entity;

use App\Entity\Traits\TId;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(
 *     name="app_household_meal",
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(columns={"household_id", "meal_id"})
 *     }
 * )
 */
class HouseholdMeal extends Entity
{

	use TId;

	/**
	 * @ORM\ManyToOne(targetEntity="Household", inversedBy="householdMeals")
	 * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
	 */
	private Household $household;

	/**
	 * @ORM\ManyToOne(targetEntity="Meal", inversedBy="householdMeals")
	 * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
	 */
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
