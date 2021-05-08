<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\TId;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="app_user")
 */
class User
{

	use TId;

	/**
	 * @ORM\Column(type="string", nullable=false)
	 */
	private string $name;

	/**
	 * @ORM\Column(type="string", nullable=false)
	 */
	private string $password;

	/**
	 * @ORM\Column(type="string", nullable=false)
	 */
	private string $email;

	/**
	 * @ORM\Column(type="boolean", nullable=false)
	 */
	private bool $verified = false;

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

}