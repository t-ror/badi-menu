<?php declare(strict_types = 1);

namespace App\Controller;

use App\Entity\User;
use App\Exception\RedirectException;
use App\Service\Household\HouseholdManager;
use App\Service\Security\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Service\Attribute\SubscribedService;

abstract class BaseController extends AbstractController
{

	public const MENU_HOUSEHOLD = 'household';
	public const MENU_MEAL = 'meal';
	public const MENU_MEAL_TAG = 'meal-tag';

	private ?string $activeMenuLink = null;

	/**
	 * @return array<string, string|SubscribedService>
	 */
	public static function getSubscribedServices(): array
	{
		$services = parent::getSubscribedServices();
		$services[UserManager::class] = '?' . UserManager::class;
		$services[HouseholdManager::class] = '?' . HouseholdManager::class;

		return $services;
	}

	/**
	 * @param mixed[] $parameters
	 */
	protected function render(string $view, array $parameters = [], ?Response $response = null): Response
	{
		$loggedInUser = $this->getLoggedInUserOrNull();
		$parameters['loggedInUser'] = $loggedInUser;
		$parameters['selectedHousehold'] = $loggedInUser !== null
			? $this->getHouseholdManager()->getSelectedHouseholdForUserOrNull($loggedInUser)
			: null;

		$parameters['activeMenuLink'] = $this->activeMenuLink;

		return parent::render(
			$view,
			$parameters,
			$response,
		);
	}

	/**
	 * @param array<string, mixed> $parameters
	 */
	protected function renderByClass(string $view, array $parameters = [], ?Response $response = null): Response
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

	/**
	 * @param array<string, mixed> $parameters
	 */
	protected function renderViewByClass(string $view, array $parameters = []): string
	{
		$classNameParsed = explode('\\', get_class($this));
		$className = array_pop($classNameParsed);
		$classNameWithoutController = str_replace('Controller', '', $className);

		return $this->renderView(
			$classNameWithoutController . '/templates/' . $view,
			$parameters,
		);
	}

	protected function checkAccessNotLoggedIn(): void
	{
		$user = $this->getLoggedInUserOrNull();
		if ($user !== null) {
			$this->redirectClean('homepage');
		}
	}

	/**
	 * @param array<string, mixed> $parameters
	 * @throws RedirectException
	 */
	public function redirectClean(string $route, array $parameters = []): never
	{
		throw new RedirectException($this->redirectToRoute($route, $parameters));
	}

	protected function getUserManager(): UserManager
	{
		return $this->container->get(UserManager::class);
	}

	protected function getLoggedInUser(): User
	{
		return $this->getUserManager()->getLoggedInUser();
	}

	protected function getLoggedInUserOrNull(): ?User
	{
		return $this->getUserManager()->getLoggedInUserOrNull();
	}

	protected function checkHouseholdSelected(): void
	{
		$user = $this->getLoggedInUserOrNull();
		if ($user === null) {
			$this->redirectClean('login');
		}

		$household = $this->getHouseholdManager()->getSelectedHouseholdForUserOrNull($user);
		if ($household === null) {
			$this->redirectClean('householdList');
		}
	}

	protected function getHouseholdManager(): HouseholdManager
	{
		return $this->container->get(HouseholdManager::class);
	}

	protected function setActiveMenuLink(?string $activeMenuLink): void
	{
		$this->activeMenuLink = $activeMenuLink;
	}

	protected function isProductionEnv(): bool
	{
		return $this->getParameter('kernel.environment') === 'prod';
	}

}
