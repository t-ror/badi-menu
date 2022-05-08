<?php declare(strict_types = 1);

namespace App\Type\Meal;

use App\Entity\MealTag;
use App\ValueObject\File\Image as ImageUtil;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class MealType extends AbstractType
{

	/**
	 * @param array<string, mixed> $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder->add('name', TextType::class, [
			'label' => 'Název *',
			'required' => true,
			'attr' => [
				'maxlength' => 255,
			],
			'constraints' => [
				new NotBlank(['message' => 'Vyplňte název']),
				new Length([
					'max' => 255,
					'maxMessage' => 'Název může obsahovat maximálně {{ limit }} znaků',
				]),
			],
		])->add('image', FileType::class, [
			'label' => 'Fotka',
			'required' => false,
			'constraints' => [
				new Image([
					'mimeTypes' => [
						ImageUtil::MIME_TYPE_JPEG,
						ImageUtil::MIME_TYPE_PNG,
					],
					'mimeTypesMessage' => 'Fotka musí být typu JPEG nebo PNG',
					'maxSize' => '10m',
					'maxSizeMessage' => 'Maximální velikost nahrané fotky může být maximálně {{ limit }} MB',
				]),
			],
		])->add('description', TextareaType::class, [
			'label' => 'Popis',
			'required' => false,
			'attr' => [
				'class' => 'app_quil-editor',
				'maxlength' => 400,
				'data-quill-height' => 135,
			],
		])->add('mealIngredients', CollectionType::class, [
			'label' => 'Ingredience',
			'entry_type' => MealIngredientType::class,
			'entry_options' => ['label' => false],
			'allow_add' => true,
			'allow_delete' => true,
		])->add('method', TextareaType::class, [
			'label' => 'Postup',
			'required' => false,
			'attr' => [
				'class' => 'app_quil-editor',
				'maxlength' => 2000,
				'data-quill-height' => 200,
			],
		])->add('mealTags', EntityType::class, [
			'label' => 'Štítky',
			'required' => false,
			'class' => MealTag::class,
			'multiple' => true,
			'query_builder' => function (EntityRepository $entityRepository): QueryBuilder {
				return $entityRepository->createQueryBuilder('mealTag')
					->orderBy('mealTag.name', 'ASC');
			},
			'choice_label' => 'name',
			'attr' => [
				'class' => 'app_select2',
			],
		])->add('ableToPrepare', CheckboxType::class, [
			'label' => 'Umím připravit',
			'required' => false,
		])->add('favorite', CheckboxType::class, [
			'label' => 'Oblíbené',
			'required' => false,
		])->add('submit', SubmitType::class, [
			'label' => 'Potvrdit',
		]);
	}

}
