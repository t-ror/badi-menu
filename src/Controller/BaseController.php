<?php declare(strict_types = 1);

namespace App\Controller;

use App\Exception\RedirectException;
use App\Service\Security\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

abstract class BaseController extends AbstractController
{

	protected UserManager $userManager;

	public function __construct(UserManager $userManager)
	{
		$this->userManager = $userManager;
	}

	protected function renderByClass(string $view, array $parameters = [], Response $response = null): Response
	{
		$classNameParsed = explode('\\', get_class($this));
		$className = array_pop($classNameParsed);
		$classNameWithoutController = str_replace('Controller', '', $className);

		return parent::render(
			$classNameWithoutController . '/templates/' . $view,
			$parameters,
			$response,
		);
	}

	protected function checkAccessLoggedIn(): void
	{
		$user = $this->userManager->getLoggedUser();
		if ($user === null) {
			throw new RedirectException($this->redirectToRoute('login'));
		}
	}

	protected function checkAccessNotLoggedIn(): void
	{
		$user = $this->userManager->getLoggedUser();
		if ($user !== null) {
			throw new RedirectException($this->redirectToRoute('homepage'));
		}
	}

}