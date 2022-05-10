<?php declare(strict_types = 1);


namespace App\Component\Meal\MealList;


use App\Component\Household\Component;
use App\Entity\Meal;
use Twig\Environment;

class MealList extends Component
{

	/** @var array<int, Meal> */
    private array $meals;
    private Environment $twig;

    /**
     * @param array<int, Meal> $meals
     */
    public function __construct(array $meals, Environment $twig)
    {
        $this->twig = $twig;
        $this->meals = $meals;
    }

    public function render(): string
    {
        return $this->twig->render($this->getTemplatePath('mealList.html.twig'), [
            'meals' => $this->meals,
        ]);
    }

}