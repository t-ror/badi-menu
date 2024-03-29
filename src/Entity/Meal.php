<?php declare(strict_types = 1);

namespace App\Entity;

use App\Entity\Traits\TId;
use App\ValueObject\File\Image;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(
 *     name="app_meal",
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(columns={"url"})
 *     }
 * )
 */
class Meal extends Entity
{

	use TId;

	/** @ORM\Column(type="string", nullable=false) */
	private string $name;

	/** @ORM\Column(type="string", nullable=false) */
	private string $url;

	/** @ORM\Column(type="string", nullable=true) */
	private ?string $image = null;

	/** @ORM\Column(type="text", nullable=true) */
	private ?string $description = null;

	/** @ORM\Column(type="text", nullable=true) */
	private ?string $method = null;

	/**
	 * @var Collection<MealIngredient>
	 * @ORM\OneToMany(targetEntity="MealIngredient", mappedBy="meal")
	 */
	private Collection $mealIngredients;

	/**
	 * @var Collection<MealTag>
	 * @ORM\ManyToMany(targetEntity="MealTag")
	 */
	private Collection $mealTags;

	/**
	 * @var Collection<HouseholdMeal>
	 * @ORM\ManyToMany(targetEntity="HouseholdMeal", mappedBy="meal")
	 */
	private Collection $householdMeals;

	/**
	 * @ORM\ManyToOne(targetEntity="User")
	 * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
	 */
	private User $createdByUser;

	public function __construct(string $name, string $url, User $createdByUser)
	{
		$this->name = $name;
		$this->url = $url;
		$this->createdByUser = $createdByUser;
		$this->mealIngredients = new ArrayCollection();
		$this->mealTags = new ArrayCollection();
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

	public function getUrl(): string
	{
		return $this->url;
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

	public function getDescription(): ?string
	{
		return $this->description;
	}

	public function setDescription(?string $description): void
	{
		$this->description = $description;
	}

	public function getMethod(): ?string
	{
		return $this->method;
	}

	public function setMethod(?string $method): void
	{
		$this->method = $method;
	}

	/**
	 * @return Collection<MealIngredient>
	 */
	public function getMealIngredients(): Collection
	{
		return $this->mealIngredients;
	}

	/**
	 * @return array<int, array<string, string|null>>
	 */
	public function getMealIngredientsArray(): array
	{
		$mealIngredientsArray = [];

		/** @var MealIngredient $mealIngredient */
		foreach ($this->mealIngredients as $mealIngredient) {
			$mealIngredientsArray[] = [
				'name' => $mealIngredient->getIngredient()->getName(),
				'amount' => $mealIngredient->getAmount(),
			];
		}

		return $mealIngredientsArray;
	}

	public function addMealIngredient(MealIngredient $mealIngredient): void
	{
		if ($this->mealIngredients->contains($mealIngredient)) {
			return;
		}

		$this->mealIngredients->add($mealIngredient);
	}

	public function containsIngredient(Ingredient $ingredient): bool
	{
		return $this->mealIngredients->exists(function (int $key, MealIngredient $mealIngredient) use ($ingredient): bool {
			return $mealIngredient->getIngredient()->getId() === $ingredient->getId();
		});
	}

	public function containsIngredientWithName(string $ingredientName): bool
	{
		return $this->mealIngredients->exists(function (int $key, MealIngredient $mealIngredient) use ($ingredientName): bool {
			return $mealIngredient->getIngredient()->getName() === $ingredientName;
		});
	}

	public function getMealIngredientWithName(string $ingredientName): ?MealIngredient
	{
		$mealIngredient = $this->mealIngredients->filter(function (MealIngredient $mealIngredient) use ($ingredientName): bool {
			return $mealIngredient->getIngredient()->getName() === $ingredientName;
		})->first();

		return $mealIngredient !== false ? $mealIngredient : null;
	}

	/**
	 * @return Collection<MealTag>
	 */
	public function getMealTags(): Collection
	{
		return $this->mealTags;
	}

	public function addMealTag(MealTag $mealTag): void
	{
		if ($this->mealTags->contains($mealTag)) {
			return;
		}

		$this->mealTags->add($mealTag);
	}

	public function removeMealTag(MealTag $mealTag): void
	{
		if (!$this->mealTags->contains($mealTag)) {
			return;
		}

		$this->mealTags->removeElement($mealTag);
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

	public function getCreatedByUser(): User
	{
		return $this->createdByUser;
	}

	public function setCreatedByUser(User $createdByUser): void
	{
		$this->createdByUser = $createdByUser;
	}

}
