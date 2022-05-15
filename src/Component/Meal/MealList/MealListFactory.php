<?php declare(strict_types = 1);

namespace App\Component\Meal\MealList;

use App\Service\Form\ListFilterFormFactory;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;

class MealListFactory
{

	private Environment $twig;
	private EntityManagerInterface $entityManager;
	private Request $request;
	private ListFilterFormFactory $listFilterFormFactory;
	private EventDispatcherInterface $eventDispatcher;

	public function __construct(
		Environment $twig,
		EntityManagerInterface $entityManager,
		RequestStack $requestStack,
		ListFilterFormFactory $listFilterFormFactory,
		EventDispatcherInterface $eventDispatcher
	)
	{
		$this->twig = $twig;
		$this->entityManager = $entityManager;
		$this->listFilterFormFactory = $listFilterFormFactory;
		$this->eventDispatcher = $eventDispatcher;

		$request = $requestStack->getCurrentRequest();
		if ($request === null) {
			throw new InvalidArgumentException('There is no request');
		}

		$this->request = $request;
	}

	public function create(): MealList
	{
		return new MealList(
			$this->twig,
			$this->entityManager,
			$this->request,
			$this->listFilterFormFactory,
			$this->eventDispatcher
		);
	}

}
