<?php declare(strict_types = 1);

namespace App\ValueObject\Lists\Filter;

abstract class Filter
{

	protected string $name;
	protected string $label;
	protected ?string $value = null;

	public function __construct(string $name, string $label)
	{
		$this->name = $name;
		$this->label = $label;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getLabel(): string
	{
		return $this->label;
	}

	public function getValue(): ?string
	{
		return $this->value;
	}

	public function setValue(?string $value): void
	{
		$this->value = $value;
	}

	public function getValueForView(): ?string
	{
		return sprintf('%s - "%s"', $this->getLabel(), $this->getValue());
	}

	public function isFilterText(): bool
	{
		return $this instanceof FilterText;
	}

	public function isFilterMultiSelect(): bool
	{
		return $this instanceof FilterMultiSelect;
	}

}
