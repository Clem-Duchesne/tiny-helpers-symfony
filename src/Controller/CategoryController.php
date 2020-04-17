<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CategoryController extends AbstractController
{
    /**
     * @Route("/category", name="category")
     */
    public function index(categoryRepository $categoryRepository)
    {
        return $this->render('category/index.html.twig',[
            'categories' => $categoryRepository->findBy([], ['id' => 'DESC'])
        ]);
    }

    /**
     * @Route("/category/add", name="category_add")
     */
    public function add(Request $request,EntityManagerInterface $em, categoryRepository $categoryRepository)
    {
        // préparer une catégorie (vierge)
        $category = new Category();
        $category->setCreatedAt( new \DateTime());
        // préparer l'objet formulaire
        $form = $this->createForm(CategoryType::class, $category);
        // Récupérer (eventuellement) les données soumises
        $form->handleRequest($request);
        // Si le formulaire a été soumis et que les données sont valides
        if ($form->isSubmitted() && $form->isValid()) {
            // enregistrer les données dans la base
            $category = $form->getData();
            $em->persist($category);
            $em->flush();
            // rediriger vers l’accueil
            return $this->redirectToRoute('index');
        }
        // formulaire non valide ou 1er acces : afficher le formulaire
        return $this->render('category/add.html.twig',
            [   'form' => $form->createView(),
                'categories' => $categoryRepository->findBy([], ['id' => 'DESC']),
            ]
        ) ;
    }
}
