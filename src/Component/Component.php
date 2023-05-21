<?php declare(strict_types = 1);

namespace App\Component;

abstract class Component
{

	public function getTemplatePath(string $name): string
	{
		$classNameParsed = explode('\\', get_class($this));
		array_pop($classNameParsed);

		$path = [];
		foreach (array_reverse($classNameParsed) as $value) {
			if ($value === 'Component') {
				break;
			}

			$path[] = $value;
		}

		$path = array_reverse($path);

		return implode(DIRECTORY_SEPARATOR, $path) . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $name;
	}

}
