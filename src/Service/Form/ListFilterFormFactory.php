<?php declare(strict_types = 1);

namespace App\Service\Form;

use App\ValueObject\Lists\Filter\Filter;
use App\ValueObject\Lists\Filter\FilterCheckBox;
use App\ValueObject\Lists\Filter\FilterCollection;
use App\ValueObject\Lists\Filter\FilterMultiSelect;
use App\ValueObject\Lists\Filter\FilterText;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\Length;

class ListFilterFormFactory
{

	public function __construct(private FormFactoryInterface $formFactory)
	{
	}

	/**
	 * @param FilterCollection<string, Filter> $filterCollection
	 */
	public function create(FilterCollection $filterCollection): FormInterface
	{
		$filterForm = $this->formFactory->create(FormType::class);

		/** @var Filter $filter */
		foreach ($filterCollection as $filter) {
			if ($filter instanceof FilterText) {
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
			} elseif ($filter instanceof FilterCheckBox) {
				$filterForm->add($filter->getName(), CheckboxType::class, [
					'data' => $filter->getValueBool(),
					'label' => $filter->getLabel(),
					'required' => false,
				]);
			} elseif ($filter instanceof FilterMultiSelect) {
				$filterForm->add($filter->getName(), ChoiceType::class, [
					'data' => $filter->getValues(),
					'label' => $filter->getLabel(),
					'required' => false,
					'multiple' => true,
					'choices' => $filter->getOptions(),
					'attr' => [
						'class' => 'app_select2',
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
