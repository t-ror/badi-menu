<?php declare(strict_types = 1);

namespace App\Type\User;

use App\Service\Security\UserManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class LoginType extends AbstractType
{

	/**
	 * @param array<string, mixed> $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder->add('login', TextType::class, [
			'label' => 'Email/Uživatelské jméno',
			'required' => true,
			'attr' => [
				'maxlength' => UserManager::USERNAME_EMAIL_MAX_LENGTH,
			],
			'constraints' => [
				new NotBlank(['message' => 'Vyplňte uživatelské jméno nebo email']),
				new Length([
					'max' => UserManager::USERNAME_EMAIL_MAX_LENGTH,
					'maxMessage' => 'Uživatelské jméno nebo email může obsahovat maximálně {{ limit }} znaků',
				]),
			],
		])->add('password', PasswordType::class, [
			'label' => 'Heslo',
			'required' => true,
		])->add('remember', CheckboxType::class, [
			'label' => 'Zapamatovat',
			'required' => false,
		])->add('submit', SubmitType::class, [
			'label' => 'Přihlásit se',
		]);
	}

}
