<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\TId;
use App\ValueObject\File\Image;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(
 *     name="app_household",
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(columns={"name"})
 *     }
 * )
 */
class Household extends Entity
{

	use TId;

	/** @ORM\Column(length=32, type="string", nullable=false) */
	private string $name;

	/** @ORM\Column(type="string", nullable=true) */
	private ?string $image = null;

	/**
	 * @var Collection<UserHousehold>
	 * @ORM\OneToMany(targetEntity="UserHousehold", mappedBy="household")
	 */
	private Collection $userHouseholds;

	public function __construct(string $name)
	{
		$this->name = $name;
		$this->userHouseholds = new ArrayCollection();
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

		return new Image($this, $this->image);
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


}