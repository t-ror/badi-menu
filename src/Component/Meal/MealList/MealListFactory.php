<?php declare(strict_types = 1);

namespace App\Component\Meal\MealList;

use App\Repository\MealTagRepository;
use App\Repository\UserRepository;
use App\Service\Form\ListFilterFormFactory;
use App\Service\Security\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;

class MealListFactory
{

	private Request $request;

	public function __construct(
		private Environment $twig,
		private EntityManagerInterface $entityManager,
		private ListFilterFormFactory $listFilterFormFactory,
		private EventDispatcherInterface $eventDispatcher,
		private UserManager $userManager,
		private MealTagRepository $mealTagRepository,
		private UserRepository $userRepository,
		RequestStack $requestStack,
	)
	{
		$request = $requestStack->getCurrentRequest();
		if ($request === null) {
			throw new InvalidArgumentException('There is no request');
		}

		$this->request = $request;
	}

	public function create(): MealList
	{
		return new MealList(
			$this->userManager->getLoggedInUser(),
			$this->twig,
			$this->entityManager,
			$this->request,
			$this->listFilterFormFactory,
			$this->eventDispatcher,
			$this->mealTagRepository,
			$this->userRepository,
		);
	}

}
