<?php declare(strict_types = 1);

namespace App\Service\Form;

use App\ValueObject\Lists\Filter\Filter;
use App\ValueObject\Lists\Filter\FilterCollection;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\Length;

class ListFilterFormFactory
{

	private FormFactoryInterface $formFactory;

	public function __construct(FormFactoryInterface $formFactory)
	{
		$this->formFactory = $formFactory;
	}

	/**
	 * @param FilterCollection<string, Filter> $filterCollection
	 */
	public function create(FilterCollection $filterCollection): FormInterface
	{
		$filterForm = $this->formFactory->create(FormType::class);
		foreach ($filterCollection as $filter) {
			if ($filter->isFilterText()) {
				$filterForm->add($filter->getName(), TextType::class, [
					'data' => $filter->getValue(),
					'label' => $filter->getLabel(),
					'required' => false,
					'attr' => [
						'maxlength' => 255,
					],
					'constraints' => [
						new Length([
							'max' => 255,
							'maxMessage' => $filter->getLabel() . ' může obsahovat maximálně {{ limit }} znaků',
						]),
					],
				]);
			}
		}

		$filterForm->add('submit', SubmitType::class, [
			'label' => 'Filtrovat',
			'attr' => [
				'style' => 'width: 100%;',
			],
		]);

		return $filterForm;
	}

}