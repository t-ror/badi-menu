<?php declare(strict_types = 1);

namespace App\Controller\MealTag;

use App\Controller\BaseController;
use App\Entity\Household;
use App\Entity\Meal;
use App\Entity\MealTag;
use App\Exception\DuplicityException;
use App\Service\Template\Vue\MealTagListItemMapper;
use App\Type\MealTag\MealTagType;
use App\Utils\Strings;
use App\ValueObject\Template\Vue\Flash;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MealTagController extends BaseController
{

	public function __construct(
		private EntityManagerInterface $entityManager,
		private MealTagListItemMapper $mealTagListItemMapper,
	)
	{
	}

	public function provideListData(): JsonResponse
	{
		$this->checkHouseholdSelected();

		$user = $this->getLoggedInUser();
		$household = $this->getHouseholdManager()->getSelectedHouseholdForUser($user);

		$mealTagCreateForm = $this->createForm(MealTagType::class);
		$creatFormView = $this->renderViewByClass('mealTagCreateForm.html.twig', [
			'mealTagForm' => $mealTagCreateForm->createView(),
		]);

		/** @var array<MealTag> $mealTags */
		$mealTags = $this->entityManager->createQueryBuilder()
			->addSelect('mealTag')
			->from(MealTag::class, 'mealTag')
			->andWhere('mealTag.household = :household')
			->setParameter('household', $household)
			->getQuery()
			->getResult();

		return $this->json([
			'createForm' => $creatFormView,
			'mealTags' => $this->mealTagListItemMapper->mapAll($mealTags),
		]);
	}

	public function list(): Response
	{
		$this->checkHouseholdSelected();
		$this->setActiveMenuLink(self::MENU_MEAL_TAG);

		return $this->renderByClass('list.html.twig');
	}

	public function delete(int $id): JsonResponse
	{
		$this->checkHouseholdSelected();

		$mealTag = $this->entityManager->find(MealTag::class, $id);
		if ($mealTag !== null) {
			$mealTagName = $mealTag->getName();

			/** @var array<Meal> $mealsWithTag */
			$mealsWithTag = $this->entityManager->createQueryBuilder()
				->addSelect('meal')
				->from(Meal::class, 'meal')
				->leftJoin('meal.mealTags', 'mealTag')
				->andWhere('mealTag = :mealTag')
				->setParameter('mealTag', $mealTag)
				->getQuery()
				->getResult();

			foreach ($mealsWithTag as $meal) {
				$meal->removeMealTag($mealTag);
			}

			$this->entityManager->remove($mealTag);

			$this->entityManager->flush();

			return $this->json([
				'flash' => Flash::createSuccess(sprintf('Štítek "%s" byl úspěšně smazán', $mealTagName)),
			]);
		}

		return $this->json('empty');
	}

	public function create(Request $request): JsonResponse
	{
		$this->checkHouseholdSelected();

		$user = $this->getLoggedInUser();
		$household = $this->getHouseholdManager()->getSelectedHouseholdForUser($user);

		$mealTagCreateForm = $this->createForm(MealTagType::class);
		$mealTagCreateForm->handleRequest($request);
		if ($mealTagCreateForm->isSubmitted() && $mealTagCreateForm->isValid()) {
			try {
				$mealTag = $this->processCreateForm($mealTagCreateForm, $household);

				return $this->json([
					'mealTag' => $this->mealTagListItemMapper->map($mealTag),
					'flash' => Flash::createSuccess('Štítek byl úspěšně vytvořen'),
				]);
			} catch (DuplicityException $duplicityException) {
				return $this->json(['flash' => Flash::createWarning($duplicityException->getMessage())]);
			}
		}

		return $this->json(['flash' => Flash::createDanger('Nastala neočekávaná chyba')]);
	}

	public function edit(Request $request): JsonResponse
	{
		$this->checkHouseholdSelected();

		$user = $this->getLoggedInUser();
		$household = $this->getHouseholdManager()->getSelectedHouseholdForUser($user);

		$mealTagEditForm = $this->createEditForm();
		$mealTagEditForm->handleRequest($request);
		if ($mealTagEditForm->isSubmitted() && $mealTagEditForm->isValid()) {
			try {
				$mealTag = $this->processEditForm($mealTagEditForm, $household);
				if ($mealTag === null) {
					return $this->json([
						'flash' => Flash::createWarning('Úprava se nezdařila. Vybraný štítek mezitím někdo smazal.'),
						'status' => 'deleted',
					]);
				}

				return $this->json([
					'mealTag' => $this->mealTagListItemMapper->map($mealTag),
					'flash' => Flash::createSuccess('Štítek byl úspěšně upraven'),
				]);
			} catch (DuplicityException $duplicityException) {
				return $this->json(['flash' => Flash::createWarning($duplicityException->getMessage())]);
			}
		}

		return $this->json(['flash' => Flash::createDanger('Nastala neočekávaná chyba'), $mealTagEditForm]);
	}

	/**
	 * @throws DuplicityException
	 */
	private function processCreateForm(FormInterface $mealTagForm, Household $household): MealTag
	{
		$values = $mealTagForm->getData();
		$name = Strings::trim($values['name']);
		if ($this->mealTagExists($name, $household)) {
			throw new DuplicityException(sprintf('Štítek s názvem "%s" už existuje', $name));
		}

		$mealTag = new MealTag($name, $household);

		$this->entityManager->persist($mealTag);

		$this->entityManager->flush();

		return $mealTag;
	}

	/**
	 * @throws DuplicityException
	 */
	private function processEditForm(FormInterface $editForm, Household $household): ?MealTag
	{
		$values = $editForm->getData();

		$mealTag = $this->entityManager->find(MealTag::class, $values['id']);
		if ($mealTag !== null) {
			$name = $values['name'];
			if ($this->mealTagExists($values['name'], $household)) {
				throw new DuplicityException(sprintf('Štítek s názvem "%s" už existuje', $name));
			}

			$mealTag->setName($name);

			$this->entityManager->flush();

			return $mealTag;
		}

		return null;
	}

	private function mealTagExists(string $name, Household $household): bool
	{
		$mealTag = $this->entityManager->getRepository(MealTag::class)->findOneBy([
			'name' => $name,
			'household' => $household,
		]);

		return $mealTag !== null;
	}

	private function createEditForm(): FormInterface
	{
		$editForm = $this->createForm(MealTagType::class);
		$editForm->add('id', HiddenType::class);

		return $editForm;
	}

}
