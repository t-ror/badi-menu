<?php declare(strict_types = 1);

namespace App\Entity;

use App\Entity\Traits\TId;
use App\Repository\HouseholdRepository;
use App\ValueObject\File\Image;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[Entity(repositoryClass: HouseholdRepository::class)]
#[Table(name: 'app_household')]
#[UniqueConstraint(columns: ['name'])]
class Household extends EntityOrm
{

	use TId;

	#[Column(type: 'string', length: 32, nullable: false)]
	private string $name;

	#[Column(type: 'string', nullable: false)]
	private ?string $image = null;

	/** @var Collection<UserHousehold> */
	#[OneToMany(targetEntity: UserHousehold::class, mappedBy: 'household')]
	private Collection $userHouseholds;

	/** @var Collection<HouseholdMeal> */
	#[OneToMany(targetEntity: HouseholdMeal::class, mappedBy: 'household')]
	private Collection $householdMeals;

	public function __construct(string $name)
	{
		$this->name = $name;
		$this->userHouseholds = new ArrayCollection();
		$this->householdMeals = new ArrayCollection();
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function setName(string $name): void
	{
		$this->name = $name;
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

	public function hasAnyMember(): bool
	{
		return $this->userHouseholds->count() > 0;
	}

	/**
	 * @return Collection<HouseholdMeal>
	 */
	public function getHouseholdMeals(): Collection
	{
		return $this->householdMeals;
	}

	public function addHouseholdMeal(HouseholdMeal $householdMeal): void
	{
		if ($this->householdMeals->contains($householdMeal)) {
			return;
		}

		$this->householdMeals->add($householdMeal);
	}

}
