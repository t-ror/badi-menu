<?php declare(strict_types = 1);

namespace App\Security;

enum Role: string
{

	case USER = 'ROLE_USER';
	case USER_VERIFIED = 'ROLE_USER_VERIFIED';

}
