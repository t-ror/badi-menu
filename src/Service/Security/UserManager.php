<?php declare(strict_types = 1);

namespace App\Service\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

class UserManager
{

	public const USERNAME_EMAIL_MIN_LENGTH = 3;
	public const USERNAME_EMAIL_MAX_LENGTH = 64;
	public const PASSWORD_MIN_LENGTH = 6;
	public const PASSWORD_MAX_LENGTH = 64;

	private PasswordHasherInterface $encoder;

	public function __construct(
		private EntityManagerInterface $entityManager,
		private Security $security,
		PasswordHasherFactoryInterface $encoderFactory,
		RequestStack $requestStack,
	)
	{
		$this->encoder = $encoderFactory->getPasswordHasher(User::class);

		$request = $requestStack->getCurrentRequest();
		if ($request === null) {
			throw new InvalidArgumentException('There is no request');
		}
	}

	public function isPasswordValid(User $user, string $password): bool
	{
		return $this->encoder->verify($user->getPassword(), $password);
	}

	public function getLoggedInUserOrNull(): ?User
	{
		$user = $this->security->getUser();
		if (!$user instanceof User && $user !== null) {
			throw new AuthenticationException('Invalid user instance');
		}

		return $user;
	}

	public function getLoggedInUser(): User
	{
		$user = $this->getLoggedInUserOrNull();
		if ($user === null) {
			throw new UserNotFoundException('No signed in user found');
		}

		return $user;
	}

	public function createUser(string $name, string $email, string $password): void
	{
		$user = new User(
			$name,
			$this->encoder->hash($password),
			$email
		);

		$this->entityManager->persist($user);
		$this->entityManager->flush();
	}

	public function setNewPassword(User $user, string $password): void
	{
		$user->setPassword($this->encoder->hash($password));
	}

}
