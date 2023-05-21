<?php declare(strict_types = 1);

namespace App\Component\MealTag\MealTagList;

use App\Component\Component;
use App\Entity\Household;
use App\Entity\MealTag;
use App\Event\FormSubmittedEvent;
use App\Type\MealTag\MealTagType;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use InvalidArgumentException;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;

class MealTagList extends Component
{

	private Household $household;
	private EntityManagerInterface $entityManager;
	private Environment $twig;
	private QueryBuilder $queryBuilder;
	private FormFactoryInterface $formFactory;
	private Request $request;
	private EventDispatcherInterface $eventDispatcher;

	public function __construct(
		Household $household,
		Environment $twig,
		EntityManagerInterface $entityManager,
		FormFactoryInterface $formFactory,
		Request $request,
		EventDispatcherInterface $eventDispatcher
	)
	{
		$this->household = $household;
		$this->entityManager = $entityManager;
		$this->twig = $twig;
		$this->queryBuilder = $this->getBaseQueryBuilder();
		$this->formFactory = $formFactory;
		$this->request = $request;
		$this->eventDispatcher = $eventDispatcher;
	}

	public function render(): string
	{
		/** @var array<MealTag> $mealTags */
		$mealTags = $this->queryBuilder->getQuery()->getResult();

		$editForms = [];
		foreach ($mealTags as $mealTag) {
			$editForm = $this->formFactory->create(MealTagType::class, [
				'name' => $mealTag->getName(),
			]);

			$editForm->add('id', HiddenType::class, [
				'data' => $mealTag->getId(),
			]);

			$editForms[$mealTag->getId()] = $editForm->createView();

			$editForm->handleRequest($this->request);
			if ($editForm->isSubmitted() && $editForm->isValid()) {
				$this->processEditForm($editForm);
			}
		}

		return $this->twig->render($this->getTemplatePath('mealTagList.html.twig'), [
			'mealTags' => $mealTags,
			'mealTagUsage' => $this->getMealTagsUsage($mealTags),
			'editForms' => $editForms,
		]);
	}

	public function orderByName(?string $sort = 'ASC'): self
	{
		if ($sort !== 'ASC' && $sort !== 'DESC') {
			throw new InvalidArgumentException('Invalid sort key');
		}

		$this->queryBuilder->addOrderBy('mealTag.name', $sort);

		return $this;
	}

	private function getBaseQueryBuilder(): QueryBuilder
	{
		return $this->entityManager->createQueryBuilder()
			->addSelect('mealTag')
			->from(MealTag::class, 'mealTag')
			->andWhere('mealTag.household = :household')
			->setParameter('household', $this->household);
	}

	/**
	 * @param array<MealTag> $mealTags
	 * @return array<int, int>
	 */
	private function getMealTagsUsage(array $mealTags): array
	{
		$mealTagIds = [];
		$result = [];
		foreach ($mealTags as $mealTag) {
			$mealTagIds[] = $mealTag->getId();
			$result[$mealTag->getId()] = 0;
		}

		$counts = $this->entityManager->getConnection()->createQueryBuilder()
			->addSelect('meal_meal_tag.meal_tag_id AS meal_tag_id')
			->addSelect('COUNT(meal_meal_tag.meal_tag_id) AS count')
			->from('meal_meal_tag')
			->andWhere('meal_meal_tag.meal_tag_id IN (:mealTagIds)')
			->setParameter('mealTagIds', $mealTagIds, Connection::PARAM_INT_ARRAY)
			->groupBy('meal_meal_tag.meal_tag_id')
			->execute()
			->fetchAllAssociative();

		foreach ($counts as $count) {
			$result[(int) $count['meal_tag_id']] = $count['count'];
		}

		return $result;
	}

	private function processEditForm(FormInterface $editForm): void
	{
		$values = $editForm->getData();

		$submittedEvent = new FormSubmittedEvent('mealTagList');

		$mealTag = $this->entityManager->find(MealTag::class, $values['id']);
		if ($mealTag !== null) {
			$mealTag->setName($values['name']);

			$this->entityManager->flush();

			$submittedEvent->addFlash('success', 'Štítek byl úspěšně upraven');
		}

		$this->eventDispatcher->dispatch($submittedEvent, FormSubmittedEvent::NAME_DEFAULT);
	}

}
