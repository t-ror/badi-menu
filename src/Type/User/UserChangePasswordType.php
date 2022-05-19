<?php declare(strict_types = 1);

namespace App\Type\User;

use App\Service\Security\UserManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;

class UserChangePasswordType extends AbstractType
{

	/**
	 * @param array<string, mixed> $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder->add('passwordOld', PasswordType::class, [
			'label' => 'Staré heslo',
			'required' => true,
			'constraints' => [
				new Length([
					'min' => UserManager::PASSWORD_MIN_LENGTH,
					'max' => UserManager::PASSWORD_MAX_LENGTH,
					'minMessage' => 'Heslo musí obsahovat minimálně {{ limit }} znaků',
					'maxMessage' => 'Heslo může obsahovat maximálně {{ limit }} znaků',
				]),
			],
		])->add('passwordNew', PasswordType::class, [
			'label' => 'Nové heslo',
			'required' => true,
			'constraints' => [
				new Length([
					'min' => UserManager::PASSWORD_MIN_LENGTH,
					'max' => UserManager::PASSWORD_MAX_LENGTH,
					'minMessage' => 'Heslo musí obsahovat minimálně {{ limit }} znaků',
					'maxMessage' => 'Heslo může obsahovat maximálně {{ limit }} znaků',
				]),
			],
		])->add('passwordNewCheck', PasswordType::class, [
			'label' => 'Nové heslo znovu',
			'required' => true,
			'constraints' => [
				new Length([
					'min' => UserManager::PASSWORD_MIN_LENGTH,
					'max' => UserManager::PASSWORD_MAX_LENGTH,
					'minMessage' => 'Heslo musí obsahovat minimálně {{ limit }} znaků',
					'maxMessage' => 'Heslo může obsahovat maximálně {{ limit }} znaků',
				]),
			],
		])->add('submit', SubmitType::class, [
			'label' => 'Změnit heslo',
		]);
	}

}
