<?php declare(strict_types = 1);

namespace App\ValueObject\Lists\Filter;

use Doctrine\Common\Collections\ArrayCollection;

class FilterCollection extends ArrayCollection
{

	public function getWithValue(): ArrayCollection
	{
		return $this->filter(function (Filter $filter): bool {
			return $filter->getValue() !== null;
		});
	}

	public function getWithoutFilter(Filter $filterToRemove): ArrayCollection
	{
		return $this->filter(function (Filter $filter) use ($filterToRemove): bool {
			return $filter !== $filterToRemove;
		});
	}

	/**
	 * @return array<string, string|null>
	 */
	public function getAsParametersArray(): array
	{
		$parameters = [];
		foreach ($this as $filter) {
			if ($filter instanceof FilterText) {
				$parameters[$filter->getName()] = $filter->getValue();
			}
		}

		return $parameters;
	}

}
