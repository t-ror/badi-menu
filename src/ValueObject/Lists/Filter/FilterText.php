<?php declare(strict_types = 1);

namespace App\ValueObject\Lists\Filter;

class FilterText extends Filter
{

	public function __construct(string $name, string $label)
	{
		parent::__construct($name, $label);
	}

}
