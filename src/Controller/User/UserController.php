<?php declare(strict_types = 1);

namespace App\Controller\User;

use App\Controller\BaseController;
use App\Entity\User;
use App\Type\User\LoginType;
use App\Type\User\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends BaseController
{

	private EntityManagerInterface $entityManager;

	public function __construct(EntityManagerInterface $entityManager)
	{
		$this->entityManager = $entityManager;
	}

	public function login(Request $request): Response {
		$this->checkAccessNotLoggedIn();

		$loginForm = $this->createForm(LoginType::class);
		$loginForm->handleRequest($request);
		if ($loginForm->isSubmitted() && $loginForm->isValid()) {
			$values = $loginForm->getData();
			$user = $this->getUserManager()->authenticateAndGetUser($values['login'], $values['password']);
			if ($user === null) {
				$this->addFlash('warning','Nesprávné uživatelské jméno nebo heslo');

				return $this->redirectToRoute('login');
			}

			if (!$user->isVerified()) {
				$this->addFlash('warning','Zadaný uživatelský účet ještě nebyl aktivován');

				return $this->redirectToRoute('login');
			}

			$response = $this->redirectToRoute('homepage');
			$this->getUserManager()->loginUser($user, $values['remember'], $response);
			$this->addFlash('success','Přihlášeno!');

			return $response;
		}

		return $this->renderByClass('login.html.twig', [
			'loginForm' => $loginForm->createView(),
		]);
	}

	public function logout(): Response {
		$this->checkAccessLoggedIn();

		$response = $this->redirectToRoute('login');
		$this->getUserManager()->logoutUser($response);

		return $response;
	}

	public function register(Request $request): Response {
		$this->checkAccessNotLoggedIn();

		$registerForm = $this->createForm(RegisterType::class);
		$registerForm->handleRequest($request);
		if ($registerForm->isSubmitted() && $registerForm->isValid()) {
			$userRepository = $this->entityManager->getRepository(User::class);
			$values = $registerForm->getData();
			$user = $userRepository->getByName($values['username']);
			if ($user !== null) {
				$this->addFlash('warning','Uživatelské jméno už je zabrané');

				return $this->redirectToRoute('register');
			}

			$user = $userRepository->getByEmail($values['email']);
			if ($user !== null) {
				$this->addFlash('warning','Uživatel se zadaným emailem již existuje');

				return $this->redirectToRoute('register');
			}

			$this->getUserManager()->createUser($values['username'], $values['email'], $values['password']);
			$this->addFlash('success','Uživatelský účet úspěšně vytvořen. Počkejte na aktivování administrátorem.');

			return $this->redirectToRoute('login');
		}

		return $this->renderByClass('register.html.twig', [
			'registerForm' => $registerForm->createView(),
		]);
	}

}