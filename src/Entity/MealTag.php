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

	public function __construct(string $name)
	{
		$this->name = $name;
	}

	public function getName(): string
	{
		return $this->name;
	}

}
