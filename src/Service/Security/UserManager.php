<?php declare(strict_types = 1);

namespace App\Service\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

class UserManager
{

	public const USERNAME_EMAIL_MIN_LENGTH = 3;
	public const USERNAME_EMAIL_MAX_LENGTH = 64;
	public const PASSWORD_MIN_LENGTH = 6;
	public const PASSWORD_MAX_LENGTH = 64;
	private const GLOBAL_USER = 'user';
	private const GLOBAL_AUTH_TOKEN = 'authToken';

	private PasswordHasherInterface $encoder;
	private SessionInterface $session;
	private Request $request;

	public function __construct(
		private EntityManagerInterface $entityManager,
		private UserRepository $userRepository,
		PasswordHasherFactoryInterface $encoderFactory,
		RequestStack $requestStack,
	)
	{
		$this->encoder = $encoderFactory->getPasswordHasher(User::class);
		$this->session = $requestStack->getSession();

		$request = $requestStack->getCurrentRequest();
		if ($request === null) {
			throw new InvalidArgumentException('There is no request');
		}
		$this->request = $request;
	}

	public function authenticateAndGetUser(string $nameOrEmail, string $password): ?User
	{
		$user = $this->userRepository->getByNameOrEmail($nameOrEmail);
		if ($user === null || !$this->isPasswordValid($user, $password)) {
			return null;
		}

		return $user;
	}

	public function isPasswordValid(User $user, string $password): bool
	{
		return $this->encoder->verify($user->getPassword(), $password);
	}

	public function loginUser(User $user, bool $remember, Response $response): void
	{
		if ($user->getToken() !== null) {
			$token = $user->getToken();
		} else {
			$token = bin2hex(random_bytes(16));
			$user->setToken($token);
		}

		if ($remember) {
			$expire = time() + (86400 * 30);
			$response->headers->setCookie(Cookie::create('user', $user->getName(), $expire));
			$response->headers->setCookie(Cookie::create('authToken', $token, $expire));
		}

		$this->session->set(self::GLOBAL_USER, $user->getName());
		$this->session->set(self::GLOBAL_AUTH_TOKEN, $token);
		$this->session->save();

		$this->entityManager->flush();
	}

	public function logoutUser(Response $response): void
	{
		$response->headers->clearCookie(self::GLOBAL_USER);
		$response->headers->clearCookie(self::GLOBAL_AUTH_TOKEN);
		$this->session->remove(self::GLOBAL_USER);
		$this->session->remove(self::GLOBAL_AUTH_TOKEN);
		$this->session->save();
	}

	public function getLoggedUserOrNull(): ?User
	{
		$name = $this->request->cookies->get(self::GLOBAL_USER) !== null
			? $this->request->cookies->get(self::GLOBAL_USER)
			: $this->session->get(self::GLOBAL_USER);

		$token = $this->request->cookies->get(self::GLOBAL_AUTH_TOKEN) !== null
			? $this->request->cookies->get(self::GLOBAL_AUTH_TOKEN)
			: $this->session->get(self::GLOBAL_AUTH_TOKEN);

		if ($name === null || $token === null) {
			return null;
		}

		/** @var User|null $user */
		$user = $this->userRepository->findOneBy([
			'name' => $name,
			'token' => $token,
		]);

		return $user;
	}

	public function getLoggedUser(): User
	{
		$user = $this->getLoggedUserOrNull();
		if ($user === null) {
			throw new UserNotFoundException();
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
