<?php declare(strict_types = 1);

namespace App\Component\Household\HouseholdList;

use App\Component\Household\Component;
use App\Entity\Household;
use Twig\Environment;

class HouseholdList extends Component
{

	/** @var array<int, Household> */
	private array $households;
	private Environment $twig;

	/**
	 * @param array<int, Household> $households
	 */
	public function __construct(array $households, Environment $twig)
	{
		$this->twig = $twig;
		$this->households = $households;
	}

	public function render(): string
	{
		return $this->twig->render($this->getTemplatePath('householdList.html.twig'), [
			'households' => $this->households,
		]);
	}

}
