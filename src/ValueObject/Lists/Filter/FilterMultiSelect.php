<?php declare(strict_types = 1);

namespace App\ValueObject\Lists\Filter;

class FilterMultiSelect extends Filter
{

	public const URL_VALUES_SEPARATOR = '-';

	/** @var array<int|string, string> */
	private array $options;

	/**
	 * @param array<int|string, string> $options
	 */
	public function __construct(string $name, string $label, array $options)
	{
		parent::__construct($name, $label);
		$this->options = $options;
	}

	/**
	 * @return array<string, string>
	 */
	public function getValuesForView(): array
	{
		if ($this->getValue() === null) {
			return [];
		}

		$result = [];
		foreach ($this->getValues() as $value) {
			$optionsFlipped = array_flip($this->getOptions());
			if (!array_key_exists($value, $optionsFlipped)) {
				continue;
			}

			$result[$value] = sprintf('"%s"', $optionsFlipped[$value]);
		}

		return $result;
	}

	/**
	 * @return array<int|string, string>
	 */
	public function getOptions(): array
	{
		return $this->options;
	}

	/**
	 * @return array<string>
	 */
	public function getValues(): array
	{
		return $this->getValue() !== null ? explode(self::URL_VALUES_SEPARATOR, $this->getValue()) : [];
	}

}
