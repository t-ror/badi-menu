<?php declare(strict_types = 1);

namespace App\Component\MealTag\MealTagList;

use App\Entity\Household;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;

class MealTagListFactory
{

	private EntityManagerInterface $entityManager;
	private Environment $twig;
	private FormFactoryInterface $formFactory;
	private Request $request;
	private EventDispatcherInterface $eventDispatcher;

	public function __construct(
		Environment $twig,
		EntityManagerInterface $entityManager,
		FormFactoryInterface $formFactory,
		RequestStack $requestStack,
		EventDispatcherInterface $eventDispatcher
	)
	{
		$this->entityManager = $entityManager;
		$this->twig = $twig;
		$this->formFactory = $formFactory;
		$this->eventDispatcher = $eventDispatcher;

		$request = $requestStack->getCurrentRequest();
		if ($request === null) {
			throw new InvalidArgumentException('There is no request');
		}

		$this->request = $request;
	}

	public function create(Household $household): MealTagList
	{
		return new MealTagList(
			$household,
			$this->twig,
			$this->entityManager,
			$this->formFactory,
			$this->request,
			$this->eventDispatcher,
		);
	}

}
