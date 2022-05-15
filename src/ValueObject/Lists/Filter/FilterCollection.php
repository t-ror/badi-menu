<?php declare(strict_types = 1);

namespace App\ValueObject\Lists\Filter;

use App\Utils\Strings;
use Doctrine\Common\Collections\ArrayCollection;

class FilterCollection extends ArrayCollection
{

	public function getWithValue(): ArrayCollection
	{
		return $this->filter(function (Filter $filter): bool {
			return $filter->getValue() !== null;
		});
	}

	/**
	 * @return array<string, string|null>
	 */
	public function getAsParametersArray(?Filter $withoutFilter = null, ?string $withoutValue = null): array
	{
		$parameters = [];

		/** @var Filter $filter */
		foreach ($this as $filter) {
			if ($filter instanceof FilterMultiSelect) {
				$values = $filter->getValues();
				if ($filter === $withoutFilter && $withoutValue !== null) {
					$keyToRemove = array_search($withoutValue, $values, true);
					if ($keyToRemove !== false) {
						unset($values[$keyToRemove]);
					}
				}

				$value = implode(FilterMultiSelect::URL_VALUES_SEPARATOR, $values);
			} else {
				if ($filter === $withoutFilter) {
					continue;
				}

				$value = $filter->getValue();
			}

			if ($value === null || Strings::isEmpty($value)) {
				continue;
			}

			$parameters[$filter->getName()] = $value;
		}

		return $parameters;
	}

}
