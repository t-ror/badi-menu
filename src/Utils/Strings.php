<?php declare(strict_types = 1);

namespace App\Utils;

use Nette\Utils\Strings as NetteStrings;

class Strings extends NetteStrings
{

	public static function isEmpty(string $string): bool
	{
		return parent::length($string) === 0;
	}

	public static function filledOrNull(string $string): ?string
	{
		return self::isEmpty($string) ? null : $string;
	}

}
