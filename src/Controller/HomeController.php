<?php

namespace App\Controller;

use App\Entity\Tool;
use App\Entity\Category;
use App\Repository\ToolRepository;
use App\Repository\UserRepository;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;


class HomeController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(categoryRepository $categoryRepository, toolRepository $toolRepository,userRepository $userRepository)
    {

       
       $category = 'all';
        return $this->render('home/index.html.twig', 
        [
            'categories' => $categoryRepository->findBy([]),
            'tools' => $toolRepository->findBy([], ['id' => 'DESC']),
            'category_name' => $category,
            'users' => $userRepository->findBy([], ['id' => 'DESC'])
        ]);
    }
    /**
     * @Route("/home/{category}", name="index_category")
     */
    
    public function indexByCategory(Request $request,categoryRepository $categoryRepository, toolRepository $toolRepository, userRepository $userRepository)
    {
       $category = $request->get('category');
        return $this->render('home/index.html.twig', 
        [
            'categories' => $categoryRepository->findBy([]),
            'tools' => $toolRepository->findBy([], ['id' => 'DESC']),
            'category_name' =>$category,
            'users' => $userRepository->findBy([], ['id' => 'DESC'])
        ]);
    }
}
