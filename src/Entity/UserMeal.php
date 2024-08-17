<?php declare(strict_types = 1);

namespace App\Entity;

use App\Entity\Traits\TId;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[Entity]
#[Table(name: 'app_user_meal')]
#[UniqueConstraint(columns: ['user_id', 'meal_id'])]
class UserMeal extends EntityOrm
{

	use TId;

	#[ManyToOne(targetEntity: User::class, inversedBy: 'userMeals')]
	#[JoinColumn(referencedColumnName: 'id', nullable: false)]
	private User $user;

	#[ManyToOne(targetEntity: Meal::class)]
	#[JoinColumn(referencedColumnName: 'id', nullable: false)]
	private Meal $meal;

	#[Column(type: 'boolean', nullable: false, options: ['default' => 0])]
	private bool $ableToPrepare = false;

	#[Column(type: 'boolean', nullable: false, options: ['default' => 0])]
	private bool $favorite = false;

	public function __construct(User $user, Meal $meal)
	{
		$this->user = $user;
		$this->meal = $meal;

		$user->addUserMeal($this);
	}

	public function getUser(): User
	{
		return $this->user;
	}

	public function getMeal(): Meal
	{
		return $this->meal;
	}

	public function isAbleToPrepare(): bool
	{
		return $this->ableToPrepare;
	}

	public function setAbleToPrepare(bool $ableToPrepare): void
	{
		$this->ableToPrepare = $ableToPrepare;
	}

	public function isFavorite(): bool
	{
		return $this->favorite;
	}

	public function setFavorite(bool $favorite): void
	{
		$this->favorite = $favorite;
	}

}
