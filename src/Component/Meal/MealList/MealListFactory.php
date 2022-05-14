<?php declare(strict_types = 1);

namespace App\Component\Meal\MealList;

use Doctrine\ORM\EntityManagerInterface;
use Twig\Environment;

class MealListFactory
{

	private Environment $twig;
	private EntityManagerInterface $entityManager;

	public function __construct(Environment $twig, EntityManagerInterface $entityManager)
	{
		$this->twig = $twig;
		$this->entityManager = $entityManager;
	}

	public function create(): MealList
	{
		return new MealList($this->twig, $this->entityManager);
	}

}
