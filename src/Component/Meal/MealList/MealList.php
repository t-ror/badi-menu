<?php declare(strict_types = 1);

namespace App\Component\Meal\MealList;

use App\Component\Household\Component;
use App\Entity\Household;
use App\Entity\Meal;
use App\Event\FormSubmittedEvent;
use App\Service\Form\ListFilterFormFactory;
use App\ValueObject\Lists\Filter\Filter;
use App\ValueObject\Lists\Filter\FilterText;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use InvalidArgumentException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;

class MealList extends Component
{

	private const FILTER_KEY_NAME = 'name';

	private QueryBuilder $queryBuilder;

	/** @var array<string, Filter> */
	private array $filters = [];

	private Environment $twig;
	private EntityManagerInterface $entityManager;
	private Request $request;
	private ListFilterFormFactory $listFilterFormFactory;
	private EventDispatcherInterface $eventDispatcher;

	public function __construct(
		Environment $twig,
		EntityManagerInterface $entityManager,
		Request $request,
		ListFilterFormFactory $listFilterFormFactory,
		EventDispatcherInterface $eventDispatcher
	)
	{
		$this->twig = $twig;
		$this->entityManager = $entityManager;
		$this->request = $request;
		$this->listFilterFormFactory = $listFilterFormFactory;

		$this->queryBuilder = $this->getBaseQueryBuilder();
		$this->eventDispatcher = $eventDispatcher;
	}

	public function render(): string
	{
		$meals = $this->queryBuilder->getQuery()->getResult();
		$filterForm = $this->listFilterFormFactory->create($this->filters);
		$filterForm->handleRequest($this->request);
		if ($filterForm->isSubmitted()) {
			$this->processFilterForm($filterForm);
		}

		return $this->twig->render($this->getTemplatePath('mealList.html.twig'), [
			'meals' => $meals,
			'filterForm' => $filterForm->createView(),
		]);
	}

	public function forHousehold(Household $household): self
	{
		$this->queryBuilder->andWhere('householdMeals.household = :household')
			->setParameter('household', $household);

		return $this;
	}

	public function orderByName(?string $sort = 'ASC'): self
	{
		if ($sort !== 'ASC' && $sort !== 'DESC') {
			throw new InvalidArgumentException('Invalid sort key');
		}

		$this->queryBuilder->addOrderBy('meal.name', $sort);

		return $this;
	}

	public function addFilterName(): self
	{
		if (array_key_exists(self::FILTER_KEY_NAME, $this->filters)) {
			throw new InvalidArgumentException('Name filter has been already added');
		}

		$this->filters[self::FILTER_KEY_NAME] = new FilterText(self::FILTER_KEY_NAME, 'Název');

		$value = $this->request->get(self::FILTER_KEY_NAME);
		if ($value !== null) {
			$this->filters[self::FILTER_KEY_NAME]->setValue($value);

			$this->queryBuilder->andWhere('meal.name LIKE :name')
				->setParameter('name', '%' . $value . '%');
		}

		return $this;
	}

	private function getBaseQueryBuilder(): QueryBuilder
	{
		return $this->entityManager->createQueryBuilder()
			->select('meal')
			->addSelect('createdByUser')
			->addSelect('mealIngredients')
			->addSelect('ingredient')
			->addSelect('mealTags')
			->from(Meal::class, 'meal')
			->leftJoin('meal.createdByUser', 'createdByUser')
			->leftJoin('meal.mealIngredients', 'mealIngredients')
			->leftJoin('mealIngredients.ingredient', 'ingredient')
			->leftJoin('meal.mealTags', 'mealTags')
			->leftJoin('meal.householdMeals', 'householdMeals');
	}

	private function processFilterForm(FormInterface $filterForm): void
	{
		$values = $filterForm->getData();
		$submittedEvent = new FormSubmittedEvent($values, 'mealList');
		$this->eventDispatcher->dispatch($submittedEvent, FormSubmittedEvent::NAME_FILTER_FORM);
	}

}
