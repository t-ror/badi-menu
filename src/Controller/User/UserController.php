<?php declare(strict_types = 1);

namespace App\Controller\User;

use App\Component\Household\UserHouseholdList\UserHouseholdListFactory;
use App\Component\Meal\MealList\MealListFactory;
use App\Controller\BaseController;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\File\ImageFacade;
use App\Type\User\LoginType;
use App\Type\User\RegisterType;
use App\Type\User\UserChangePasswordType;
use App\Type\User\UserEditType;
use App\Utils\UserUrl;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends BaseController
{

	private UserRepository $userRepository;
	private MealListFactory $mealListFactory;
	private UserHouseholdListFactory $userHouseholdListFactory;
	private ImageFacade $imageFacade;
	private EntityManagerInterface $entityManager;

	public function __construct(
		EntityManagerInterface $entityManager,
		MealListFactory $mealListFactory,
		UserHouseholdListFactory $userHouseholdListFactory,
		ImageFacade $imageFacade
	)
	{
		$this->entityManager = $entityManager;
		$this->userRepository = $entityManager->getRepository(User::class);
		$this->mealListFactory = $mealListFactory;
		$this->userHouseholdListFactory = $userHouseholdListFactory;
		$this->imageFacade = $imageFacade;
	}

	public function login(Request $request): Response
	{
		$this->checkAccessNotLoggedIn();
		dump($this->entityManager->getConnection());

		$loginForm = $this->createForm(LoginType::class);
		$loginForm->handleRequest($request);
		if ($loginForm->isSubmitted() && $loginForm->isValid()) {
			$values = $loginForm->getData();
			$user = $this->getUserManager()->authenticateAndGetUser($values['login'], $values['password']);
			if ($user === null) {
				$this->addFlash('warning', 'Nesprávné uživatelské jméno nebo heslo');

				return $this->redirectToRoute('login');
			}

			if (!$user->isVerified()) {
				$this->addFlash('warning', 'Zadaný uživatelský účet ještě nebyl aktivován');

				return $this->redirectToRoute('login');
			}

			$response = $this->redirectToRoute('homepage');
			$this->getUserManager()->loginUser($user, $values['remember'], $response);

			return $response;
		}

		return $this->renderByClass('login.html.twig', [
			'loginForm' => $loginForm->createView(),
		]);
	}

	public function logout(): Response
	{
		$this->checkAccessLoggedIn();

		$response = $this->redirectToRoute('login');
		$this->getUserManager()->logoutUser($response);

		return $response;
	}

	public function register(Request $request): Response
	{
		$this->checkAccessNotLoggedIn();

		$registerForm = $this->createForm(RegisterType::class);
		$registerForm->handleRequest($request);
		if ($registerForm->isSubmitted() && $registerForm->isValid()) {
			$values = $registerForm->getData();
			$user = $this->userRepository->getByName($values['username']);
			if ($user !== null) {
				$this->addFlash('warning', 'Uživatelské jméno už je zabrané');

				return $this->redirectToRoute('register');
			}

			$user = $this->userRepository->getByEmail($values['email']);
			if ($user !== null) {
				$this->addFlash('warning', 'Uživatel se zadaným emailem již existuje');

				return $this->redirectToRoute('register');
			}

			$this->getUserManager()->createUser($values['username'], $values['email'], $values['password']);
			$this->addFlash('success', 'Uživatelský účet úspěšně vytvořen. Počkejte na aktivování administrátorem.');

			return $this->redirectToRoute('login');
		}

		return $this->renderByClass('register.html.twig', [
			'registerForm' => $registerForm->createView(),
		]);
	}

	public function detail(string $url): Response
	{
		$this->checkAccessLoggedIn();
		$userUrl = UserUrl::createFromUrl($url);

		$user = $this->userRepository->getByUserUrl($userUrl);
		if ($user === null) {
			return $this->redirectToRoute('homepage');
		}

		$mealListFavorite = $this->mealListFactory->create()->forUserFavorite($user);

		return $this->renderByClass('detail.html.twig', [
			'user' => $user,
			'mealListFavorite' => $mealListFavorite->render(),
			'userHouseholdList' => $this->userHouseholdListFactory->create($user)->renderPreview(),
		]);
	}

	public function edit(Request $request): Response
	{
		$this->checkAccessLoggedIn();

		$user = $this->getUserManager()->getLoggedUser();
		$userEditForm = $this->createForm(UserEditType::class, ['email' => $user->getEmail()]);
		$userEditForm->handleRequest($request);
		if ($userEditForm->isSubmitted() && $userEditForm->isValid()) {
			$this->processUserEditForm($userEditForm, $user);

			return $this->redirectToRoute('userEdit');
		}

		$userChangePasswordForm = $this->createForm(UserChangePasswordType::class);
		$userChangePasswordForm->handleRequest($request);
		if ($userChangePasswordForm->isSubmitted() && $userChangePasswordForm->isValid()) {
			$this->processUserChangePasswordForm($userChangePasswordForm, $user);

			return $this->redirectToRoute('userEdit');
		}

		return $this->renderByClass('edit.html.twig', [
			'user' => $user,
			'userEditForm' => $userEditForm->createView(),
			'userChangePasswordForm' => $userChangePasswordForm->createView(),
		]);
	}

	private function processUserEditForm(FormInterface $form, User $user): void
	{
		$values = $form->getData();

		$user->setEmail($values['email']);

		/** @var UploadedFile|null $imageFile */
		$imageFile = $values['image'];
		if ($imageFile !== null) {
			$image = $this->imageFacade->saveAndOverwrite(
				$imageFile,
				User::class,
				$user->getId(),
				$imageFile->getClientOriginalName()
			);

			$user->setImage($image);
		}

		$this->entityManager->flush();

		$this->addFlash('success', 'Informace úspěšně upraveny');
	}

	private function processUserChangePasswordForm(FormInterface $form, User $user): void
	{
		$values = $form->getData();
		if (!$this->getUserManager()->isPasswordValid($user, $values['passwordOld'])) {
			$this->addFlash('warning', 'Zadali jste špatně staré heslo');

			$this->redirectClean('userEdit');
		}

		if ($values['passwordNew'] !== $values['passwordNewCheck']) {
			$this->addFlash('warning', 'Hesla se neshodují');

			$this->redirectClean('userEdit');
		}

		$this->getUserManager()->setNewPassword($user, $values['passwordNew']);

		$this->entityManager->flush();

		$this->addFlash('success', 'Heslo úspěšně změněno');
	}

}
