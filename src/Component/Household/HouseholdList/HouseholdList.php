<?php declare(strict_types = 1);

namespace App\Component\Household\HouseholdList;

use App\Component\Component;
use App\Entity\Household;
use Twig\Environment;

class HouseholdList extends Component
{

	/**
	 * @param array<Household> $households
	 */
	public function __construct(
		private array $households,
		private Environment $twig,
	)
	{
	}

	public function render(): string
	{
		return $this->twig->render($this->getTemplatePath('householdList.html.twig'), [
			'households' => $this->households,
		]);
	}

}
