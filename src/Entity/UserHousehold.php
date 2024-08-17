<?php declare(strict_types = 1);

namespace App\Entity;

use App\Entity\Traits\TId;
use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Entity]
#[Table(name: 'app_user_household')]
class UserHousehold extends EntityOrm
{

	use TId;

	#[ManyToOne(targetEntity: User::class, inversedBy: 'userHouseholds')]
	#[JoinColumn(referencedColumnName: 'id', nullable: false)]
	private User $user;

	#[ManyToOne(targetEntity: Household::class, inversedBy: 'userHouseholds')]
	#[JoinColumn(referencedColumnName: 'id', nullable: false)]
	private Household $household;

	#[Column(type: 'datetime_immutable', nullable: false)]

	private DateTimeImmutable $dateJoined;

	#[Column(type: 'datetime_immutable', nullable: true)]
	private ?DateTimeImmutable $dateLastSelected = null;

	#[Column(type: 'boolean', nullable: false, options: ['default' => 0])]
	private bool $allowed = false;

	#[Column(type: 'boolean', nullable: false, options: ['default' => 0])]
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
