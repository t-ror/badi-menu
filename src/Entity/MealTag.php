<?php declare(strict_types = 1);

namespace App\Entity;

use App\Entity\Traits\TId;
use App\Repository\MealTagRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Entity(repositoryClass: MealTagRepository::class)]
#[Table(name: 'app_meal_tag')]
class MealTag extends EntityOrm
{

	use TId;

	#[Column(type: 'string', length: 32, nullable: false)]
	private string $name;

	#[ManyToOne(targetEntity: Household::class, inversedBy: 'householdMeals')]
	#[JoinColumn(referencedColumnName: 'id', nullable: false)]
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

	public function setName(string $name): void
	{
		$this->name = $name;
	}

	public function getHousehold(): Household
	{
		return $this->household;
	}

}
