<?php

namespace App\Controller;

use App\Entity\Tool;
use App\Repository\ToolRepository;
use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Cocur\Slugify\Slugify;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CategoryController extends AbstractController
{
    
    /**
     * @Route("/category", name="category")
     */
    public function index(categoryRepository $categoryRepository, toolRepository $toolRepository)
    {
        return $this->render('category/index.html.twig',[
            'categories' => $categoryRepository->findBy([], ['id' => 'DESC']),
            'tools' => $toolRepository->findBy([], ['id' => 'DESC']),
            'category_name' => 'all'
        ]);
    }

    /**
     * @Route("/category/add", name="category_add")
     */
    public function add(Request $request,EntityManagerInterface $em, categoryRepository $categoryRepository,toolRepository $toolRepository)
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
            $slugify = new Slugify();

            $slug_category = $slugify->slugify($category);
            $category->setSlug($slug_category);
            $em->persist($category);
            $em->flush();
            // rediriger vers l’accueil
            return $this->redirectToRoute('index');
        }
        // formulaire non valide ou 1er acces : afficher le formulaire
        return $this->render('category/add.html.twig',
            [   'form' => $form->createView(),
                'categories' => $categoryRepository->findAll(),
                'tools' => $toolRepository->findAll(),
                'category_name' => 'all'
            ]
        ) ;
    }

    /**
     * @Route("/category/{id}/delete", name="category_delete")
     */
    public function delete(Category $category, EntityManagerInterface $em)
    {
        $em->remove($category);
        $em->flush();
        return $this->redirectToRoute('category');
    }
}
