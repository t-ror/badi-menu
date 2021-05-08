<?php declare(strict_types = 1);

namespace App\Controller\Login;

use App\Controller\BaseController;
use App\Controller\Login\types\LoginType;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LoginController extends BaseController
{

	public function index(Request $request): Response {
		$loginForm = $this->createForm(LoginType::class);

		$loginForm->handleRequest($request);

		if ($loginForm->isSubmitted() && $loginForm->isValid()) {
			$values = $loginForm->getData();

			/** @var User|null $user */
			$user = $this->getEntityManager()->getRepository(User::class)->findOneBy([
				'name' => $values['login'],
				'password' => $values['password'],
			]);

			if ($user === null) {
				$this->addFlash('warning','Nesprávné uživatelské jméno nebo heslo');
			} else {
				$this->addFlash('success','Přihlášeno!');
			}

			return $this->redirectToRoute('index');
		}

		return $this->render('Login/templates/login.html.twig', [
			'loginForm' => $loginForm->createView(),
		]);
	}

}