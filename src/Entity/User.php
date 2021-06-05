<?php declare(strict_types = 1);

namespace App\Entity;

use App\Entity\Traits\TId;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(
 *     name="app_user",
 *     uniqueConstraints={
 *          @UniqueConstraint(columns={"name"}),
 *          @UniqueConstraint(columns={"email"})
 *     }
 * )
 */
class User
{

	use TId;

	/**
	 * @ORM\Column(length=32, type="string", nullable=false)
	 */
	private string $name;

	/**
	 * @ORM\Column(type="string", nullable=false)
	 */
	private string $password;

	/**
	 * @ORM\Column(length=64, type="string", nullable=false)
	 */
	private string $email;

	/**
	 * @ORM\Column(type="boolean", nullable=false)
	 */
	private bool $verified = false;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	private ?string $token = null;

	public function __construct(string $name, string $password, string $email)
	{
		$this->name = $name;
		$this->password = $password;
		$this->email = $email;
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

}