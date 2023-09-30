<?php declare(strict_types = 1);

namespace App\Exception;

use Exception;

class DuplicityException extends Exception
{

	public function __construct(string $message)
	{
		parent::__construct($message);
	}

}
