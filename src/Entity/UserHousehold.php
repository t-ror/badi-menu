<?php declare(strict_types = 1);

namespace App\Entity;

use App\Entity\Traits\TId;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="app_user_household")
 */
class UserHousehold extends Entity
{

	use TId;

	/**
	 * @ORM\ManyToOne(targetEntity="User", inversedBy="UserHouseholds")
	 * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
	 */
	private User $user;

	/**
	 * @ORM\ManyToOne(targetEntity="Household", inversedBy="UserHouseholds")
	 * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
	 */
	private Household $household;

	/** @ORM\Column(type="datetime_immutable", nullable=false) */
	private DateTimeImmutable $dateJoined;

	/** @ORM\Column(type="datetime_immutable", nullable=true) */
	private ?DateTimeImmutable $dateLastSelected = null;

	/** @ORM\Column(type="boolean", options={"default":0}, nullable=false) */
	private bool $allowed = false;

	/** @ORM\Column(type="boolean", options={"default":0}, nullable=false) */
	private bool $allowedToCook = false;

	public function __construct(User $user, Household $household)
	{
		$this->user = $user;
		$this->household = $household;
		$this->dateJoined = new DateTimeImmutable();

		$user->addUserHousehold($this);
		$household->addUserHousehold($this);
	}

	public function getUser(): User
	{
		return $this->user;
	}

	public function getHousehold(): Household
	{
		return $this->household;
	}

	public function getDateJoined(): DateTimeImmutable
	{
		return $this->dateJoined;
	}

	public function setDateJoined(DateTimeImmutable $dateJoined): void
	{
		$this->dateJoined = $dateJoined;
	}

	public function isAllowed(): bool
	{
		return $this->allowed;
	}

	public function setAllowed(bool $allowed): void
	{
		$this->allowed = $allowed;
	}

	public function isAllowedToCook(): bool
	{
		return $this->allowedToCook;
	}

	public function setAllowedToCook(bool $allowedToCook): void
	{
		$this->allowedToCook = $allowedToCook;
	}

	public function getDateLastSelected(): ?DateTimeImmutable
	{
		return $this->dateLastSelected;
	}

	public function setDateLastSelected(?DateTimeImmutable $dateLastSelected): void
	{
		$this->dateLastSelected = $dateLastSelected;
	}

}
