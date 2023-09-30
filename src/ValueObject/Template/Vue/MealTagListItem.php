<?php declare(strict_types = 1);

namespace App\ValueObject\Template\Vue;

class MealTagListItem
{

	private int $id;
	private string $name;
	private int $usage;
	private string $deleteUrl;
	private string $showMealsUrl;
	private string $editUrl;
	private string $editFormHtml;

	public function __construct(
		int $id,
		string $name,
		int $usage,
		string $deleteUrl,
		string $showMealsUrl,
		string $editUrl,
		string $editFormHtml
	)
	{
		$this->id = $id;
		$this->name = $name;
		$this->usage = $usage;
		$this->deleteUrl = $deleteUrl;
		$this->showMealsUrl = $showMealsUrl;
		$this->editUrl = $editUrl;
		$this->editFormHtml = $editFormHtml;
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getUsage(): int
	{
		return $this->usage;
	}

	public function getDeleteUrl(): string
	{
		return $this->deleteUrl;
	}

	public function getShowMealsUrl(): string
	{
		return $this->showMealsUrl;
	}

	public function getEditUrl(): string
	{
		return $this->editUrl;
	}

	public function getEditFormHtml(): string
	{
		return $this->editFormHtml;
	}

}
