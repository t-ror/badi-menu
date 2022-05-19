<?php declare(strict_types = 1);

namespace App\Type\User;

use App\Service\Security\UserManager;
use App\ValueObject\File\Image as ImageUtil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserEditType extends AbstractType
{

	/**
	 * @param array<string, mixed> $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder->add('email', TextType::class, [
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
		])->add('submit', SubmitType::class, [
			'label' => 'Upravit',
		]);
	}

}
