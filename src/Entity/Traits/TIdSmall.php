<?php declare(strict_types = 1);

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

trait TIdSmall
{

	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="smallint", nullable=false)
	 */
	private int $id;

	public function getId(): int
	{
		return $this->id;
	}

}
