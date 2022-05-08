<?php declare(strict_types = 1);

namespace App\Entity;

use App\Entity\Traits\TId;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(
 *     name="app_user_meal",
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(columns={"user_id", "meal_id"})
 *     }
 * )
 */
class UserMeal extends Entity
{

	use TId;

	/**
	 * @ORM\ManyToOne(targetEntity="User", inversedBy="userMeals")
	 * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
	 */
	private User $user;

	/**
	 * @ORM\ManyToOne(targetEntity="Meal")
	 * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
	 */
	private Meal $meal;

	/** @ORM\Column(type="boolean", nullable=false, options={"default" : 0}) */
	private bool $ableToPrepare = false;

	/** @ORM\Column(type="boolean", nullable=false, options={"default" : 0}) */
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
