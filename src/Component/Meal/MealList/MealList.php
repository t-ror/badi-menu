<?php declare(strict_types = 1);

namespace App\Component\Meal\MealList;

use App\Component\Household\Component;
use App\Entity\Household;
use App\Entity\Meal;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use InvalidArgumentException;
use Twig\Environment;

class MealList extends Component
{

	private QueryBuilder $queryBuilder;

	private Environment $twig;
	private EntityManagerInterface $entityManager;

	public function __construct(Environment $twig, EntityManagerInterface $entityManager)
	{
		$this->twig = $twig;
		$this->entityManager = $entityManager;

		$this->queryBuilder = $this->getBaseQueryBuilder();
	}

	public function render(): string
	{
		$meals = $this->queryBuilder->getQuery()->getResult();

		return $this->twig->render($this->getTemplatePath('mealList.html.twig'), [
			'meals' => $meals,
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

}
