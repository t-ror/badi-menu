<?php declare(strict_types = 1);

namespace App\Controller\Meal;

use App\Controller\BaseController;
use App\Entity\Meal;
use App\Service\File\ImageFacade;
use App\Service\Meal\MealIngredientManager;
use App\Service\Meal\UserMealManager;
use App\Type\Meal\MealType;
use App\Utils\Strings;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MealController extends BaseController
{

	private EntityManagerInterface $entityManager;
	private MealIngredientManager $mealIngredientManager;
	private ImageFacade $imageFacade;
	private UserMealManager $userMealManager;

	public function __construct(
		EntityManagerInterface $entityManager,
		MealIngredientManager $mealIngredientManager,
		ImageFacade $imageFacade,
		UserMealManager $userMealManager
	)
	{
		$this->entityManager = $entityManager;
		$this->mealIngredientManager = $mealIngredientManager;
		$this->imageFacade = $imageFacade;
		$this->userMealManager = $userMealManager;
	}

	public function list(): Response
	{
		$this->checkAccessLoggedIn();

		return $this->renderByClass('list.html.twig');
	}

	public function create(Request $request): Response
	{
		$this->checkAccessLoggedIn();
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

	private function processCreateForm(FormInterface $form): void
	{
		$values = $form->getData();

		$loggedUser = $this->getUserManager()->getLoggedUser();
		$meal = new Meal($values['name'], $loggedUser);
		$meal->setDescription($values['description']);
		$meal->setMethod($values['method']);

		foreach ($values['mealIngredients'] as $mealIngredient) {
			$this->mealIngredientManager->addIngredientToMealByName(
				$meal,
				$mealIngredient['name'],
				Strings::filledOrNull($mealIngredient['amount'])
			);
		}

		foreach ($values['mealTags'] as $mealTag) {
			$meal->addMealTag($mealTag);
		}

		$this->entityManager->persist($meal);
		$this->entityManager->flush();

		if ($values['ableToPrepare']) {
			$this->userMealManager->addAbleToPrepare($loggedUser, $meal);
		}

		if ($values['favorite']) {
			$this->userMealManager->addFavorite($loggedUser, $meal);
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

}
