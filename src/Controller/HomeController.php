<?php

namespace App\Controller;

use App\Entity\Tool;
use App\Entity\Category;
use App\Repository\ToolRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(categoryRepository $categoryRepository, toolRepository $toolRepository)
    {
        return $this->render('home/index.html.twig', 
        [
            'categories' => $categoryRepository->findBy([], ['id' => 'DESC']),
            'tools' => $toolRepository->findBy([], ['id' => 'DESC'])
        ]);
    }
}
