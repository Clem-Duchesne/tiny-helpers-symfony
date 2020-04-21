<?php

namespace App\Controller;

use App\Entity\Tool;
use App\Form\ToolType;
use App\Entity\Category;
use Cocur\Slugify\Slugify;
use App\Form\CategoryType;
use App\Repository\ToolRepository;
use App\Repository\CategoryRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class ToolController extends AbstractController
{
    /**
     * @Route("/tool", name="tool")
     */
    public function index(categoryRepository $categoryRepository, toolRepository $toolRepository, userRepository $userRepository)
    {
        return $this->render('tool/index.html.twig', [
            'categories' => $categoryRepository->findBy([], ['id' => 'DESC']),
            'tools' => $toolRepository->findBy([], ['id' => 'DESC']),
            'users' => $userRepository->findBy([], ['id' => 'DESC'])
        ]);
    }

    /**
     * @Route("/tool/add", name="tool_add")
     */
    public function add(Request $request,EntityManagerInterface $em, categoryRepository $categoryRepository, toolRepository $toolRepository)
    {
        $tool = new Tool();
        $tool->setCreatedAt( new \DateTime());
        // préparer l'objet formulaire
        $form = $this->createForm(ToolType::class, $tool);
        // Récupérer (eventuellement) les données soumises
        $form->handleRequest($request);
        // Si le formulaire a été soumis et que les données sont valides
        if ($form->isSubmitted() && $form->isValid()) {
            
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('file')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($imageFile) {
                $slugify = new Slugify();
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugify->slugify($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $imageFile->move(
                        $this->getParameter('upload_dir'),
                        $newFilename
                    );
                }
                catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $tool->setImage($newFilename);
            }
            // enregistrer les données dans la base
            $tool = $form->getData();
            $em->persist($tool);
            $em->flush();
            // rediriger vers l’accueil
            return $this->redirectToRoute('tool');
        }
        // formulaire non valide ou 1er acces : afficher le formulaire
        return $this->render('tool/add.html.twig',
            [   'form' => $form->createView(),
                'categories' => $categoryRepository->findBy([], ['id' => 'DESC']),
                'tools' => $toolRepository->findBy([], ['id' => 'DESC'])
            ]
        ) ;
    }

    /**
     * Modifier un outil.
     *
     * @Route("/tool/{id}/edit", name="tool_edit", methods={"GET","POST"})
     */
    public function update(Request $request, EntityManagerInterface $em, Tool $tool,  categoryRepository $categoryRepository, toolRepository $toolRepository) : Response
    {   
        $form = $this->createForm(ToolType::class, $tool);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('file')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($imageFile) {
                $slugify = new Slugify();
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugify->slugify($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $imageFile->move(
                        $this->getParameter('upload_dir'),
                        $newFilename
                    );
                }
                catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $tool->setImage($newFilename);
            }
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('tool');
        }

        return $this->render('tool/edit.html.twig', [
            'form' => $form->createView(),
            'categories' => $categoryRepository->findBy([], ['id' => 'DESC']),
            'tools' => $toolRepository->findBy([], ['id' => 'DESC']),
            'tool' => $tool
        ]);
    }

    /**
     * @Route("/tool/{id}/delete", name="tool_delete")
     */
    public function delete(Tool $tool, EntityManagerInterface $em)
    {
        $em->remove($tool);
        $em->flush();
        return $this->redirectToRoute('tool');
    }
}
