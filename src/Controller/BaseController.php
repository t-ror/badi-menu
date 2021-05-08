<?php declare(strict_types=1);

namespace App\Controller;

use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class BaseController extends AbstractController
{

	protected function getEntityManager(): ObjectManager
	{
		return $this->getDoctrine()->getManager();
	}

}