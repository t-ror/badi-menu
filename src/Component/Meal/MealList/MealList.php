<?php declare(strict_types = 1);

namespace App\Component\Meal\MealList;

use App\Component\Household\Component;
use App\Entity\Household;
use App\Entity\Meal;
use App\Entity\MealTag;
use App\Entity\User;
use App\Entity\UserMeal;
use App\Event\FormSubmittedEvent;
use App\Repository\MealTagRepository;
use App\Repository\UserRepository;
use App\Service\Form\ListFilterFormFactory;
use App\ValueObject\Lists\Filter\Filter;
use App\ValueObject\Lists\Filter\FilterCheckBox;
use App\ValueObject\Lists\Filter\FilterCollection;
use App\ValueObject\Lists\Filter\FilterMultiSelect;
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
	private const FILTER_KEY_MEAL_TAGS = 'mealTags';
	private const FILTER_CAN_BE_PREPARED_BY = 'canBePreparedBy';
	private const FILTER_FAVORITE = 'favorite';

	private QueryBuilder $queryBuilder;
	private ?Household $household = null;

	/** @var FilterCollection<Filter> */
	private FilterCollection $filters;

	private Environment $twig;
	private EntityManagerInterface $entityManager;
	private Request $request;
	private ListFilterFormFactory $listFilterFormFactory;
	private EventDispatcherInterface $eventDispatcher;
	private MealTagRepository $mealTagRepository;
	private UserRepository $userRepository;
	private User $user;

	public function __construct(
		User $user,
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
		$this->eventDispatcher = $eventDispatcher;
		$this->filters = new FilterCollection();

		$this->queryBuilder = $this->getBaseQueryBuilder();
		$this->mealTagRepository = $entityManager->getRepository(MealTag::class);
		$this->userRepository = $entityManager->getRepository(User::class);
		$this->user = $user;
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
			'filters' => $this->filters,
			'loggedUser' => $this->user,
		]);
	}

	public function forHousehold(Household $household): self
	{
		$this->queryBuilder->andWhere('householdMeals.household = :household')
			->setParameter('household', $household);

		$this->household = $household;

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
		if ($this->filters->containsKey(self::FILTER_KEY_NAME)) {
			throw new InvalidArgumentException('Name filter has been already added');
		}

		$filter = new FilterText(self::FILTER_KEY_NAME, 'Název');

		$value = $this->request->get(self::FILTER_KEY_NAME);
		if ($value !== null) {
			$filter->setValue($value);

			$this->queryBuilder->andWhere('meal.name LIKE :name')
				->setParameter('name', '%' . $value . '%');
		}

		$this->filters->add($filter);

		return $this;
	}

	public function addFilterMealTags(): self
	{
		if ($this->filters->containsKey(self::FILTER_KEY_MEAL_TAGS)) {
			throw new InvalidArgumentException('Name filter has been already added');
		}

		$filter = new FilterMultiSelect(self::FILTER_KEY_MEAL_TAGS, 'Štítky', $this->mealTagRepository->findPairs());

		$values = $this->request->get(self::FILTER_KEY_MEAL_TAGS);
		if ($values !== null) {
			$filter->setValue($values);
			$expr = $this->entityManager->getExpressionBuilder();

			foreach (explode(FilterMultiSelect::URL_VALUES_SEPARATOR, $values) as $value) {
				$mealWithTagExists = $expr->exists(
					$this->entityManager->createQueryBuilder()
						->select('1')
						->from(Meal::class, 'meal' . $value)
						->leftJoin('meal.mealTags', 'mealTags' . $value)
						->where('mealTags' . $value . '.id = :mealTagId' . $value)
				);

				$this->queryBuilder->andWhere($mealWithTagExists)
					->setParameter(':mealTagId' . $value, $value);
			}
		}

		$this->filters->add($filter);

		return $this;
	}

	public function addFilterCanBePreparedBy(): self
	{
		if ($this->filters->containsKey(self::FILTER_CAN_BE_PREPARED_BY)) {
			throw new InvalidArgumentException('Name filter has been already added');
		}

		$filter = new FilterMultiSelect(self::FILTER_CAN_BE_PREPARED_BY, 'Umí připravit', $this->userRepository->findPairs($this->household, $this->user));

		$value = $this->request->get(self::FILTER_CAN_BE_PREPARED_BY);
		if ($value !== null) {
			$filter->setValue($value);
			$expr = $this->entityManager->getExpressionBuilder();
			$values = explode(FilterMultiSelect::URL_VALUES_SEPARATOR, $value);

			$userMealExists = $expr->exists(
				$this->entityManager->createQueryBuilder()
					->select('1')
					->from(UserMeal::class, 'userMeal')
					->where('userMeal.meal = meal')
					->andWhere('userMeal.user IN (:userIds)')
					->andWhere('userMeal.ableToPrepare = 1')
			);

			$this->queryBuilder->andWhere($userMealExists)
				->setParameter(':userIds', $values);
		}

		$this->filters->add($filter);

		return $this;
	}

	public function addFilterFavorite(): self
	{
		if ($this->filters->containsKey(self::FILTER_FAVORITE)) {
			throw new InvalidArgumentException('Name filter has been already added');
		}

		$filter = new FilterCheckBox(self::FILTER_FAVORITE, 'Oblíbené');

		$value = $this->request->get(self::FILTER_FAVORITE);
		if ($value === FilterCheckBox::BOOL_TRUE) {
			$filter->setValue($value);
			$expr = $this->entityManager->getExpressionBuilder();
			$userMealExists = $expr->exists(
				$this->entityManager->createQueryBuilder()
					->select('1')
					->from(UserMeal::class, 'userMeal2')
					->where('userMeal2.meal = meal')
					->andWhere('userMeal2.user = :user')
					->andWhere('userMeal2.favorite = 1')
			);

			$this->queryBuilder->andWhere($userMealExists)
				->setParameter(':user', $this->user);
		}

		$this->filters->add($filter);

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
		$parameters = [];
		foreach ($values as $key => $value) {
			if ($value === null) {
				continue;
			}

			if (is_array($value)) {
				if (count($value) > 0) {
					$parameters[$key] = implode(FilterMultiSelect::URL_VALUES_SEPARATOR, $value);
				}

				continue;
			}

			$parameters[$key] = $value;
		}

		$submittedEvent = new FormSubmittedEvent($parameters, 'mealList');
		$this->eventDispatcher->dispatch($submittedEvent, FormSubmittedEvent::NAME_FILTER_FORM);
	}

}
