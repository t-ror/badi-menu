<?php declare(strict_types = 1);

namespace App\Controller;

use App\Exception\RedirectException;
use App\Service\Security\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

abstract class BaseController extends AbstractController
{

	public static function getSubscribedServices() {
		$services = parent::getSubscribedServices();
		$services[UserManager::class] = '?' . UserManager::class;

		return $services;
	}

	protected function render(string $view, array $parameters = [], Response $response = null): Response
	{
		$loggedUser = $this->getUserManager()->getLoggedUser();
		$parameters['loggedUser'] = $loggedUser;

		return parent::render(
			$view,
			$parameters,
			$response,
		);
	}

	protected function renderByClass(string $view, array $parameters = [], Response $response = null): Response
	{
		$classNameParsed = explode('\\', get_class($this));
		$className = array_pop($classNameParsed);
		$classNameWithoutController = str_replace('Controller', '', $className);

		return $this->render(
			$classNameWithoutController . '/templates/' . $view,
			$parameters,
			$response,
		);
	}

	protected function checkAccessLoggedIn(): void
	{
		$user = $this->getUserManager()->getLoggedUser();
		if ($user === null) {
			throw new RedirectException($this->redirectToRoute('login'));
		}
	}

	protected function checkAccessNotLoggedIn(): void
	{
		$user = $this->getUserManager()->getLoggedUser();
		if ($user !== null) {
			throw new RedirectException($this->redirectToRoute('homepage'));
		}
	}

	protected function getUserManager(): UserManager
	{
		return $this->container->get(UserManager::class);
	}

}