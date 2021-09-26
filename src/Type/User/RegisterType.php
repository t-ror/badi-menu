<?php declare(strict_types=1);

namespace App\Type\User;

use App\Service\Security\UserManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegisterType extends AbstractType
{

	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder->add('username', TextType::class, [
			'label' => 'Uživatelské jméno',
			'required' => true,
			'attr' => [
				'maxlength' => UserManager::USERNAME_EMAIL_MAX_LENGTH,
			],
			'constraints' => [
				new NotBlank(['message' => 'Vyplňte uživatelské jméno']),
				new Length([
					'min' => UserManager::USERNAME_EMAIL_MIN_LENGTH,
					'max' => UserManager::USERNAME_EMAIL_MAX_LENGTH,
					'minMessage' => 'Uživatelské jméno musí obsahovat minimálně {{ limit }} znaků',
					'maxMessage' => 'Uživatelské jméno může obsahovat maximálně {{ limit }} znaků',
				]),
			]
		])->add('email', TextType::class, [
			'label' => 'Email',
			'required' => true,
			'attr' => [
				'maxlength' => UserManager::USERNAME_EMAIL_MAX_LENGTH,
			],
			'constraints' => [
				new NotBlank(['message' => 'Vyplňte email']),
				new Length([
					'min' => UserManager::USERNAME_EMAIL_MIN_LENGTH,
					'max' => UserManager::USERNAME_EMAIL_MAX_LENGTH,
					'minMessage' => 'Email musí obsahovat minimálně {{ limit }} znaků',
					'maxMessage' => 'Email může obsahovat maximálně {{ limit }} znaků',
				]),
				new Email(['message' => 'Zadejte email ve správném formátu']),
			],
		])->add('password', PasswordType::class, [
			'label' => 'Heslo',
			'required' => true,
			'constraints' => [
				new NotBlank(['message' => 'Vyplňte uživatelské jméno nebo email']),
				new Length([
					'min' => UserManager::PASSWORD_MIN_LENGTH,
					'max' => UserManager::PASSWORD_MAX_LENGTH,
					'minMessage' => 'Heslo musí obsahovat minimálně {{ limit }} znaků',
					'maxMessage' => 'Heslo může obsahovat maximálně {{ limit }} znaků',
				]),
			],
		])->add('submit', SubmitType::class, [
			'label' => 'Zaregistrovat',
		]);
	}
}