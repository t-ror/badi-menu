<?php declare(strict_types = 1);

namespace App\Controller\MealTag;

use App\Component\MealTag\MealTagList\MealTagListFactory;
use App\Controller\BaseController;
use App\Entity\Household;
use App\Entity\Meal;
use App\Entity\MealTag;
use App\Type\MealTag\MealTagType;
use App\Utils\Strings;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MealTagController extends BaseController
{

	private MealTagListFactory $mealTagListFactory;
	private EntityManagerInterface $entityManager;

	public function __construct(MealTagListFactory $mealTagListFactory, EntityManagerInterface $entityManager)
	{
		$this->mealTagListFactory = $mealTagListFactory;
		$this->entityManager = $entityManager;
	}

	public function list(Request $request): Response
	{
		$this->checkAccessLoggedIn();
		$this->checkHouseholdSelected();
		$this->setActiveMenuLink(self::MENU_MEAL_TAG);

		$user = $this->getUserManager()->getLoggedUser();
		$household = $this->getHouseholdManager()->getSelectedHouseholdForUser($user);

		$mealTagList = $this->mealTagListFactory->create($household)
			->orderByName();

		$mealTagCreateForm = $this->createForm(MealTagType::class);
		$mealTagCreateForm->handleRequest($request);
		if ($mealTagCreateForm->isSubmitted() && $mealTagCreateForm->isValid()) {
			$this->processCreateForm($mealTagCreateForm, $household);

			return $this->redirectToRoute('mealTagList');
		}

		return $this->renderByClass('list.html.twig', [
			'mealTagForm' => $mealTagCreateForm->createView(),
			'mealTagList' => $mealTagList->render(),
		]);
	}

	public function delete(int $id): Response
	{
		$this->checkAccessLoggedIn();
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

			$this->addFlash('success', sprintf('Štítek "%s" byl úspěšně smazán', $mealTagName));
		}

		return $this->redirectToRoute('mealTagList');
	}

	private function processCreateForm(FormInterface $mealTagForm, Household $household): void
	{
		$values = $mealTagForm->getData();
		$name = Strings::trim($values['name']);
		if ($this->mealTagExists($name, $household)) {
			$this->addFlash('warning', sprintf('Štítek s názvem "%s" už existuje', $name));

			return;
		}

		$mealTag = new MealTag($name, $household);

		$this->entityManager->persist($mealTag);

		$this->entityManager->flush();

		$this->addFlash('success', 'Štítek byl úspěšně vytvořen');
	}

	private function mealTagExists(string $name, Household $household): bool
	{
		$mealTag = $this->entityManager->getRepository(MealTag::class)->findOneBy([
			'name' => $name,
			'household' => $household,
		]);

		return $mealTag !== null;
	}

}
