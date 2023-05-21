<?php declare(strict_types = 1);

namespace App\Type\MealTag;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class MealTagType extends AbstractType
{

	/**
	 * @param array<string, mixed> $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder->add('name', TextType::class, [
			'label' => 'Název',
			'required' => true,
			'attr' => [
				'maxlength' => 32,
			],
			'constraints' => [
				new NotBlank(['message' => 'Vyplňte název']),
				new Length([
					'max' => 32,
					'maxMessage' => 'Název může obsahovat maximálně {{ limit }} znaků',
				]),
			],
		])->add('submit', SubmitType::class, [
			'label' => 'Potvrdit',
		]);
	}

}
