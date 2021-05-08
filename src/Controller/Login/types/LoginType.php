<?php declare(strict_types=1);

namespace App\Controller\Login\types;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class LoginType extends AbstractType
{

	/**
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder->add('login', TextType::class, [
			'label' => 'Uživatelské jméno nebo email',
			'required' => true,
		])->add('password', PasswordType::class, [
			'label' => 'Heslo',
			'required' => true,
		])->add('submit', SubmitType::class, [
			'label' => 'Přihlásit se',
		]);
	}

}