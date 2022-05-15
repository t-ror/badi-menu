<?php declare(strict_types = 1);

namespace App\ValueObject\Lists\Filter;

class FilterCheckBox extends Filter
{

	public const BOOL_TRUE = '1';

	public function setValue(?string $value): void
	{
		parent::setValue($value === self::BOOL_TRUE ? $value : null);
	}

	public function getValueBool(): bool
	{
		return $this->getValue() !== null;
	}

	public function getValueForView(): ?string
	{
		return $this->getValueBool() ? $this->getLabel() : null;
	}

}
