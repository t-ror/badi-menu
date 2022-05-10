<?php declare(strict_types = 1);

namespace App\Component\Meal\MealList;

use App\Entity\Meal;
use Twig\Environment;

class MealListFactory
{

	private Environment $twig;

	public function __construct(Environment $twig)
	{
		$this->twig = $twig;
	}

	/**
	 * @param array<int, Meal> $meals
	 */
	public function create(array $meals): MealList
	{
		return new MealList($meals, $this->twig);
	}

}
