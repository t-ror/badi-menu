<?php declare(strict_types = 1);

namespace App\Controller\Meal;

use App\Component\Meal\MealList\MealListFactory;
use App\Controller\BaseController;
use App\Entity\Household;
use App\Entity\HouseholdMeal;
use App\Entity\Meal;
use App\Entity\User;
use App\Service\File\ImageFacade;
use App\Service\Meal\MealIngredientManager;
use App\Service\Meal\UserMealManager;
use App\Type\Meal\MealType;
use App\Utils\Strings;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MealController extends BaseController
{

	public function __construct(
		private EntityManagerInterface $entityManager,
		private MealIngredientManager $mealIngredientManager,
		private ImageFacade $imageFacade,
		private UserMealManager $userMealManager,
		private MealListFactory $mealListFactory
	)
	{
	}

	public function list(): Response
	{
		$this->checkAccessLoggedIn();
		$this->checkHouseholdSelected();
		$this->setActiveMenuLink(self::MENU_MEAL);

		$user = $this->getUserManager()->getLoggedUser();
		$household = $this->getHouseholdManager()->getSelectedHouseholdForUser($user);
		$mealList = $this->mealListFactory->create()
			->forHousehold($household)
			->orderByName()
			->addFilterName()
			->addFilterMealTags()
			->addFilterCanBePreparedBy()
			->addFilterFavorite();

		return $this->renderByClass('list.html.twig', [
			'mealList' => $mealList->render(),
		]);
	}

	public function create(Request $request): Response
	{
		$this->checkAccessLoggedIn();
		$this->checkHouseholdSelected();
		$this->setActiveMenuLink(self::MENU_MEAL);

		$mealForm = $this->createForm(MealType::class);
		$mealForm->handleRequest($request);
		if ($mealForm->isSubmitted() && $mealForm->isValid()) {
			$this->processCreateForm($mealForm);

			return $this->redirectToRoute('mealList');
		}

		return $this->renderByClass('create.html.twig', [
			'mealForm' => $mealForm->createView(),
		]);
	}

	public function edit(Request $request, string $url): Response
	{
		$this->checkAccessLoggedIn();
		$this->checkHouseholdSelected();
		$this->setActiveMenuLink(self::MENU_MEAL);

		$user = $this->getUserManager()->getLoggedUser();
		$household = $this->getHouseholdManager()->getSelectedHouseholdForUser($user);
		$meal = $this->findMealForUrl($url, $household);
		if ($meal === null) {
			$this->addFlash('warning', 'Vybrané jídlo nebylo nalezeno');

			return $this->redirectToRoute('mealList');
		}

		$user = $this->getUserManager()->getLoggedUser();
		$mealForm = $this->createForm(MealType::class, [
			'name' => $meal->getName(),
			'description' => $meal->getDescription(),
			'mealIngredients' => $meal->getMealIngredientsArray(),
			'method' => $meal->getMethod(),
			'mealTags' => $meal->getMealTags(),
			'ableToPrepare' => $user->isAbleToPrepareMeal($meal),
			'favorite' => $user->isMealFavourite($meal),
		], ['household' => $household]);

		$mealForm->handleRequest($request);
		if ($mealForm->isSubmitted() && $mealForm->isValid()) {
			$this->processEditForm($mealForm, $meal, $user);

			return $this->redirectToRoute('mealDetail', ['url' => $url]);
		}

		return $this->renderByClass('edit.html.twig', [
			'mealForm' => $mealForm->createView(),
			'meal' => $meal,
		]);
	}

	public function detail(string $url): Response
	{
		$this->checkAccessLoggedIn();
		$this->checkHouseholdSelected();
		$this->setActiveMenuLink(self::MENU_MEAL);

		$user = $this->getUserManager()->getLoggedUser();
		$household = $this->getHouseholdManager()->getSelectedHouseholdForUser($user);
		$meal = $this->findMealForUrl($url, $household);
		if ($meal === null) {
			$this->addFlash('warning', 'Vybrané jídlo nebylo nalezeno');

			return $this->redirectToRoute('mealList');
		}

		return $this->renderByClass('detail.html.twig', [
			'meal' => $meal,
		]);
	}

	public function toggleFavorite(string $url): RedirectResponse
	{
		$this->checkAccessLoggedIn();
		$this->checkHouseholdSelected();
		$this->setActiveMenuLink(self::MENU_MEAL);

		$user = $this->getUserManager()->getLoggedUser();
		$household = $this->getHouseholdManager()->getSelectedHouseholdForUser($user);
		$meal = $this->findMealForUrl($url, $household);
		if ($meal === null) {
			$this->addFlash('warning', 'Vybrané jídlo nebylo nalezeno');

			return $this->redirectToRoute('mealList');
		}

		$this->userMealManager->toggleFavorite($user, $meal);
		$this->entityManager->flush();

		return $this->redirectToRoute('mealDetail', ['url' => $meal->getUrl()]);
	}

	public function toggleAbleToPrepare(string $url): RedirectResponse
	{
		$this->checkAccessLoggedIn();
		$this->checkHouseholdSelected();
		$this->setActiveMenuLink(self::MENU_MEAL);

		$user = $this->getUserManager()->getLoggedUser();
		$household = $this->getHouseholdManager()->getSelectedHouseholdForUser($user);
		$meal = $this->findMealForUrl($url, $household);
		if ($meal === null) {
			$this->addFlash('warning', 'Vybrané jídlo nebylo nalezeno');

			return $this->redirectToRoute('mealList');
		}

		$this->userMealManager->toggleAbleToPrepare($user, $meal);
		$this->entityManager->flush();

		return $this->redirectToRoute('mealDetail', ['url' => $meal->getUrl()]);
	}

	private function processEditForm(FormInterface $form, Meal $meal, User $user): void
	{
		$values = $form->getData();

		$meal->setName($values['name']);
		$meal->setDescription($values['description']);
		$meal->setMethod($values['method']);

		foreach ($meal->getMealIngredients() as $mealIngredient) {
			$this->entityManager->remove($mealIngredient);
		}
		$meal->getMealIngredients()->clear();

		foreach ($values['mealIngredients'] as $mealIngredient) {
			$this->mealIngredientManager->addIngredientToMealByName(
				$meal,
				$mealIngredient['name'],
				$mealIngredient['amount']
			);
		}

		foreach ($values['mealTags'] as $mealTag) {
			$meal->addMealTag($mealTag);
		}

		if ((bool) $values['ableToPrepare']) {
			$this->userMealManager->addAbleToPrepare($user, $meal);
		} else {
			$this->userMealManager->removeAbleToPrepare($user, $meal);
		}

		if ((bool) $values['favorite']) {
			$this->userMealManager->addFavorite($user, $meal);
		} else {
			$this->userMealManager->removeFavorite($user, $meal);
		}

		/** @var UploadedFile|null $imageFile */
		$imageFile = $values['image'];
		if ($imageFile !== null) {
			$image = $this->imageFacade->saveAndOverwrite(
				$imageFile,
				Meal::class,
				$meal->getId(),
				$imageFile->getClientOriginalName()
			);

			$meal->setImage($image);
		}

		$this->entityManager->flush();

		$this->addFlash('success', 'Jídlo bylo úspěšně upraveno');
	}

	private function processCreateForm(FormInterface $form): void
	{
		$values = $form->getData();

		$user = $this->getUserManager()->getLoggedUser();
		$household = $this->getHouseholdManager()->getSelectedHouseholdForUser($user);

		$meal = new Meal($values['name'], $this->generateUrlForMeal($values['name']), $user);
		$meal->setDescription($values['description']);
		$meal->setMethod($values['method']);

		$householdMeal = new HouseholdMeal($household, $meal);
		$this->entityManager->persist($householdMeal);

		foreach ($values['mealIngredients'] as $mealIngredient) {
			$this->mealIngredientManager->addIngredientToMealByName(
				$meal,
				$mealIngredient['name'],
				$mealIngredient['amount']
			);
		}

		foreach ($values['mealTags'] as $mealTag) {
			$meal->addMealTag($mealTag);
		}

		$this->entityManager->persist($meal);
		$this->entityManager->flush();

		if ((bool) $values['ableToPrepare']) {
			$this->userMealManager->addAbleToPrepare($user, $meal);
		}

		if ((bool) $values['favorite']) {
			$this->userMealManager->addFavorite($user, $meal);
		}

		$this->entityManager->flush();

		/** @var UploadedFile|null $imageFile */
		$imageFile = $values['image'];
		if ($imageFile !== null) {
			$image = $this->imageFacade->saveAndOverwrite(
				$imageFile,
				Meal::class,
				$meal->getId(),
				$imageFile->getClientOriginalName()
			);

			$meal->setImage($image);

			$this->entityManager->flush();
		}

		$this->addFlash('success', 'Jídlo bylo úspěšně vytvořeno');
	}

	private function generateUrlForMeal(string $mealName): string
	{
		$randomHash = bin2hex(random_bytes(4));
		$date = (new DateTime())->format('Ymd');

		return Strings::webalize(
			sprintf('%s-%s%s', $mealName, $date, $randomHash)
		);
	}

	private function findMealForUrl(string $url, Household $household): ?Meal
	{
		return $this->entityManager->createQueryBuilder()
			->select('meal')
			->addSelect('mealIngredient')
			->addSelect('ingredient')
			->addSelect('householdMeals')
			->from(Meal::class, 'meal')
			->leftJoin('meal.mealIngredients', 'mealIngredient')
			->leftJoin('mealIngredient.ingredient', 'ingredient')
			->leftJoin('meal.householdMeals', 'householdMeals')
			->where('meal.url = :url')
			->setParameter('url', $url)
			->andWhere('householdMeals.household = :household')
			->setParameter('household', $household)
			->getQuery()
			->getOneOrNullResult();
	}

}
