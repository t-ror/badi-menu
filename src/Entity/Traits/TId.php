<?php declare(strict_types = 1);

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;

trait TId
{

	#[Id]
	#[GeneratedValue]
	#[Column(type: 'integer', nullable: false)]
	private int $id;

	public function getId(): int
	{
		return $this->id;
	}

}
