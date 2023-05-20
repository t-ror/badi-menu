<?php declare(strict_types = 1);

namespace App\Entity;

use App\Entity\Traits\TId;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MealTagRepository")
 * @ORM\Table(name="app_meal_tag")
 */
class MealTag extends Entity
{

	use TId;

	/** @ORM\Column(length=32, type="string", nullable=false) */
	private string $name;

	/**
	 * @ORM\ManyToOne(targetEntity="Household", inversedBy="householdMeals")
	 * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
	 */
	private Household $household;

	public function __construct(string $name, Household $household)
	{
		$this->name = $name;
		$this->household = $household;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getHousehold(): Household
	{
		return $this->household;
	}

}
