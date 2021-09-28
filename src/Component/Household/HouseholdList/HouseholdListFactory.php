<?php declare(strict_types = 1);

namespace App\Component\Household\HouseholdList;

use App\Entity\Household;
use Twig\Environment;

class HouseholdListFactory
{

	private Environment $twig;

	public function __construct(Environment $twig)
	{
		$this->twig = $twig;
	}

	/**
	 * @param array<int, Household> $households
	 */
	public function create(array $households): HouseholdList
	{
		return new HouseholdList($households, $this->twig);
	}

}
