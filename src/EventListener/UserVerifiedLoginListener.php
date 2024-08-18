<?php declare(strict_types = 1);

namespace App\EventListener;

use App\Exception\UserNotVerifiedException;
use App\Security\Role;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;

final class UserVerifiedLoginListener
{

	public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
	{
		$token = $event->getAuthenticationToken();
		$user = $event->getAuthenticationToken()->getUser();

		if ($user !== null && !in_array(Role::USER_VERIFIED->value, $user->getRoles(), true)) {
			$exception = new UserNotVerifiedException('User account is not verified.');
			$exception->setToken($token);

			throw $exception;
		}
	}

}
