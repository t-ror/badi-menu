<?php declare(strict_types = 1);

namespace App\Controller\Meal;

use App\Component\Meal\MealList\MealListFactory;
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
    private MealListFactory $mealListFactory;

    public function __construct(
		EntityManagerInterface $entityManager,
		MealIngredientManager $mealIngredientManager,
		ImageFacade $imageFacade,
		UserMealManager $userMealManager,
        MealListFactory $mealListFactory
	)
	{
		$this->entityManager = $entityManager;
		$this->mealIngredientManager = $mealIngredientManager;
		$this->imageFacade = $imageFacade;
		$this->userMealManager = $userMealManager;
        $this->mealListFactory = $mealListFactory;
    }

	public function list(): Response
	{
		$this->checkAccessLoggedIn();

		$meals = $this->entityManager->createQueryBuilder()
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
            ->getQuery()
            ->getResult();

		return $this->renderByClass('list.html.twig', [
            'mealList' => $this->mealListFactory->create($meals)->render(),
        ]);
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
