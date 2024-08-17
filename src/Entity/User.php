<?php declare(strict_types = 1);

namespace App\Entity;

use App\Entity\Collection\UserMealCollection;
use App\Entity\Traits\TId;
use App\Utils\UserUrl;
use App\ValueObject\File\Image;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[Entity]
#[Table(name: 'app_user')]
#[UniqueConstraint(columns: ['name'])]
#[UniqueConstraint(columns: ['email'])]
class User extends EntityOrm
{

	use TId;

	#[Column(type: 'string', length: 32, nullable: false)]
	private string $name;

	#[Column(type: 'string', nullable: false)]
	private string $password;

	#[Column(type: 'string', length: 64, nullable: false)]
	private string $email;

	#[Column(type: 'boolean', nullable: false, options: ['default' => 0])]
	private bool $verified = false;

	#[Column(type: 'string', nullable: true)]
	private ?string $token = null;

	#[Column(type: 'string', nullable: true)]
	private ?string $image = null;

	/** @var Collection<UserHousehold> */
	#[OneToMany(targetEntity: UserHousehold::class, mappedBy: 'user')]
	private Collection $userHouseholds;

	/** @var UserMealCollection<UserMeal> */
	#[OneToMany(targetEntity: UserMeal::class, mappedBy: 'user')]
	private Collection $userMeals;

	public function __construct(string $name, string $password, string $email)
	{
		$this->name = $name;
		$this->password = $password;
		$this->email = $email;

		$this->userHouseholds = new ArrayCollection();
		$this->userMeals = new UserMealCollection();
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getPassword(): string
	{
		return $this->password;
	}

	public function setPassword(string $password): void
	{
		$this->password = $password;
	}

	public function getEmail(): string
	{
		return $this->email;
	}

	public function setEmail(string $email): void
	{
		$this->email = $email;
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

	public function getUrl(): string
	{
		return UserUrl::createFromUser($this)->getUrl();
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

	public function getUserMeals(): UserMealCollection
	{
		return new UserMealCollection($this->userMeals->toArray());
	}

	public function getUserMealByMeal(Meal $meal): ?UserMeal
	{
		$userMeal = $this->getUserMeals()->filter(function (UserMeal $userMeal) use ($meal): bool {
			return $userMeal->getMeal()->getId() === $meal->getId();
		})->first();

		return $userMeal === false ? null : $userMeal;
	}

	public function isAbleToPrepareMeal(Meal $meal): bool
	{
		return $this->getUserMeals()->hasMealAbleToPrepare($meal);
	}

	public function isMealFavourite(Meal $meal): bool
	{
		return $this->getUserMeals()->hasMealFavourite($meal);
	}

}
