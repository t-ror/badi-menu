<?php declare(strict_types = 1);

namespace App\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

final class UserNotVerifiedException extends AuthenticationException
{

}
