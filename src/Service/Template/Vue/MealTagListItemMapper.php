<?php declare(strict_types = 1);

namespace App\Service\Template\Vue;

use App\Entity\MealTag;
use App\Type\MealTag\MealTagType;
use App\ValueObject\Template\Vue\MealTagListItem;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class MealTagListItemMapper
{

	private EntityManagerInterface $entityManager;
	private UrlGeneratorInterface $urlGenerator;
	private FormFactoryInterface $formFactory;
	private Environment $environment;

	public function __construct(
		EntityManagerInterface $entityManager,
		UrlGeneratorInterface $urlGenerator,
		FormFactoryInterface $formFactory,
		Environment $environment
	)
	{
		$this->entityManager = $entityManager;
		$this->urlGenerator = $urlGenerator;
		$this->formFactory = $formFactory;
		$this->environment = $environment;
	}

	public function map(MealTag $mealTag): MealTagListItem
	{
		$mealTagsUsage = $this->getMealTagsUsage([$mealTag]);

		return new MealTagListItem(
			$mealTag->getId(),
			$mealTag->getName(),
			$mealTagsUsage[$mealTag->getId()],
			$this->urlGenerator->generate('mealTagDelete', ['id' => $mealTag->getId()]),
			$this->urlGenerator->generate('mealList', ['mealTags' => $mealTag->getId()]),
			$this->urlGenerator->generate('mealTagEdit', ['id' => $mealTag->getId()]),
			$this->creatEditFormHtml($mealTag)
		);
	}

	/**
	 * @param array<MealTag> $mealTags
	 * @return array<MealTagListItem>
	 */
	public function mapAll(array $mealTags): array
	{
		$mealTagListItems = [];

		$mealTagsUsage = $this->getMealTagsUsage($mealTags);
		foreach ($mealTags as $mealTag) {
			$mealTagListItems[] = new MealTagListItem(
				$mealTag->getId(),
				$mealTag->getName(),
				$mealTagsUsage[$mealTag->getId()],
				$this->urlGenerator->generate('mealTagDelete', ['id' => $mealTag->getId()]),
				$this->urlGenerator->generate('mealList', ['mealTags' => $mealTag->getId()]),
				$this->urlGenerator->generate('mealTagEdit', ['id' => $mealTag->getId()]),
				$this->creatEditFormHtml($mealTag)
			);
		}

		return $mealTagListItems;
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
			->setParameter('mealTagIds', $mealTagIds, ArrayParameterType::INTEGER)
			->groupBy('meal_meal_tag.meal_tag_id')
			->executeQuery()
			->fetchAllAssociative();

		foreach ($counts as $count) {
			$result[(int) $count['meal_tag_id']] = (int) $count['count'];
		}

		return $result;
	}

	private function creatEditFormHtml(MealTag $mealTag): string
	{
		$editForm = $this->formFactory->create(MealTagType::class, [
			'name' => $mealTag->getName(),
		]);

		$editForm->add('id', HiddenType::class, [
			'data' => $mealTag->getId(),
		]);

		return $this->environment->render('Vue/templates/mealTagEditForm.html.twig', [
			'editForm' => $editForm->createView(),
		]);
	}

}
