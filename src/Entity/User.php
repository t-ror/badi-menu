<?php declare(strict_types = 1);

namespace App\Entity;

use App\Entity\Traits\TId;
use App\ValueObject\File\Image;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(
 *     name="app_user",
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(columns={"name"}),
 *          @ORM\UniqueConstraint(columns={"email"})
 *     }
 * )
 */
class User extends Entity
{

	use TId;

	/** @ORM\Column(length=32, type="string", nullable=false) */
	private string $name;

	/** @ORM\Column(type="string", nullable=false) */
	private string $password;

	/** @ORM\Column(length=64, type="string", nullable=false) */
	private string $email;

	/** @ORM\Column(type="boolean", options={"default":0}, nullable=false) */
	private bool $verified = false;

	/** @ORM\Column(type="string", nullable=true) */
	private ?string $token = null;

	/** @ORM\Column(type="string", nullable=true) */
	private ?string $image = null;

	/**
	 * @var Collection<UserHousehold>
	 * @ORM\OneToMany(targetEntity="UserHousehold", mappedBy="user")
	 */
	private Collection $userHouseholds;

	/**
	 * @var Collection<UserMeal>
	 * @ORM\OneToMany(targetEntity="UserMeal", mappedBy="user")
	 */
	private Collection $userMeals;

	public function __construct(string $name, string $password, string $email)
	{
		$this->name = $name;
		$this->password = $password;
		$this->email = $email;

		$this->userHouseholds = new ArrayCollection();
		$this->userMeals = new ArrayCollection();
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getPassword(): string
	{
		return $this->password;
	}

	public function getEmail(): string
	{
		return $this->email;
	}

	public function isVerified(): bool
	{
		return $this->verified;
	}

	public function getToken(): ?string
	{
		return $this->token;
	}

	public function setToken(?string $token): void
	{
		$this->token = $token;
	}

	public function addUserHousehold(UserHousehold $userHousehold): void
	{
		if ($this->userHouseholds->contains($userHousehold)) {
			return;
		}

		$this->userHouseholds->add($userHousehold);
	}

	/**
	 * @return Collection<UserHousehold>
	 */
	public function getUserHouseholds(): Collection
	{
		return $this->userHouseholds;
	}

	public function getImage(): ?Image
	{
		if ($this->image === null) {
			return null;
		}

		return new Image(self::class, $this->id, $this->image);
	}

	public function setImage(?Image $image): void
	{
		$this->image = $image !== null ? $image->getFileName() : null;
	}

	/**
	 * @return Collection<UserHousehold>
	 */
	public function getUserHouseholdsOrdered(): Collection
	{
		$userHouseholdsArray = $this->userHouseholds->toArray();
		usort($userHouseholdsArray, function (UserHousehold $userHouseholdA, UserHousehold $userHouseholdB): int {
			$orderingA = $userHouseholdA->getDateLastSelected();
			$orderingB = $userHouseholdB->getDateLastSelected();

			if ($orderingA === null && !$userHouseholdA->isAllowed()) {
				return 1;
			}

			if ($orderingB === null && !$userHouseholdB->isAllowed()) {
				return -1;
			}

			if ($orderingA === $orderingB) {
				return 0;
			}

			return $orderingA > $orderingB ? -1 : 1;
		});

		return new ArrayCollection($userHouseholdsArray);
	}

	public function getUserHouseholdWithHouseholdId(int $householdId): ?UserHousehold
	{
		$userHouseHold = $this->userHouseholds->filter(function (UserHousehold $userHousehold) use ($householdId): bool {
			return $userHousehold->getHousehold()->getId() === $householdId;
		})->first();

		return $userHouseHold !== false ? $userHouseHold : null;
	}

	public function addUserMeal(UserMeal $userMeal): void
	{
		if ($this->userMeals->contains($userMeal)) {
			return;
		}

		$this->userMeals->add($userMeal);
	}

	public function getUserMeals(): Collection
	{
		return $this->userMeals;
	}

	public function getUserMealByMeal(Meal $meal): ?UserMeal
	{
		$userMeal = $this->userMeals->filter(function (UserMeal $userMeal) use ($meal): bool {
			return $userMeal->getMeal()->getId() === $meal->getId();
		})->first();

		return $userMeal === false ? null : $userMeal;
	}

}
