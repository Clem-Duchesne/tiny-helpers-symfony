<?php

namespace App\Controller;

use App\Service\Functions;
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
            'users' => $userRepository->findBy([], ['id' => 'DESC']),
            'category_name' => 'all'
        ]);
    }

    /**
     * @Route("/tool/add", name="tool_add")
     */
    public function add(Request $request,EntityManagerInterface $em, categoryRepository $categoryRepository, toolRepository $toolRepository, Functions $functions)
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
                $uploadDir = $this->getParameter('upload_dir');
                    // updates the 'brochureFilename' property to store the PDF file name
                    // instead of its contents
                $tool->setImage($functions->addImage($imageFile, $uploadDir));
            }
            else{
                $fileName = $form->get('name')->getData();
                $url = $form->get('link')->getData();
                $arrContextOptions=array(
                    "ssl"=>array(
                        "cafile" => "../config/cacert.pem",
                        "verify_peer"=> true,
                        "verify_peer_name"=> true,
                    ),
                );

                $params = http_build_query(array(
                    "access_key" => "6146018387b84ac9abcd7b565241cf31",
                    "url" => $url
                ));
                
                $image_data = file_get_contents("https://api.apiflash.com/v1/urltoimage?" . $params, false, stream_context_create($arrContextOptions));
                file_put_contents($this->getParameter('upload_dir') . '/' . $fileName, $image_data);
                $functions->redimensionner_image( $this->getParameter('upload_dir') . '/' . $fileName, 345);
                $tool->setImage($fileName);
            }
            // enregistrer les données dans la base
            
           $user = $form->get('user')->getData();
           $user[]= $this->getUser();
           
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
                'tools' => $toolRepository->findBy([], ['id' => 'DESC']),
                'session' => $this->getUser()->getId(),
                'category_name' => 'all'
            ]
        ) ;
    }

    /**
     * Modifier un outil.
     *
     * @Route("/tool/{id}/edit", name="tool_edit", methods={"GET","POST"})
     */
    public function update(Request $request, EntityManagerInterface $em, Tool $tool,  categoryRepository $categoryRepository, toolRepository $toolRepository, Functions $functions) : Response
    {   
        $oldUrl = $tool->getLink();
        $form = $this->createForm(ToolType::class, $tool);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
           
            $imageFile = $form->get('file')->getData();
            $url = $form->get('link')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($imageFile) {
                $uploadDir = $this->getParameter('upload_dir');
                    // updates the 'brochureFilename' property to store the PDF file name
                    // instead of its contents
                $oldImage = $tool->getImage();
                $tool->setImage($functions->addImage($imageFile, $uploadDir));
                $functions->deleteImage($oldImage);
            }
            //dd($url . '               ' . $oldUrl);
            elseif($url != $oldUrl){
                $fileName = $form->get('name')->getData() . '.jpeg';
                $arrContextOptions=array(
                    "ssl"=>array(
                        "cafile" => "../config/cacert.pem",
                        "verify_peer"=> true,
                        "verify_peer_name"=> true,
                    ),
                );

                $params = http_build_query(array(
                    "access_key" => "6146018387b84ac9abcd7b565241cf31",
                    "url" => $url
                ));
                
                $image_data = file_get_contents("https://api.apiflash.com/v1/urltoimage?" . $params, false, stream_context_create($arrContextOptions));
                file_put_contents($this->getParameter('upload_dir') . '/' . $fileName, $image_data);
                $functions->redimensionner_image( $this->getParameter('upload_dir') . '/' . $fileName, 345);
                $tool->setImage($fileName);
            }
            $user = $form->get('user')->getData();
            $user[]= $this->getUser();
           
            $tool = $form->getData();
            
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('tool');
        }

        return $this->render('tool/edit.html.twig', [
            'form' => $form->createView(),
            'categories' => $categoryRepository->findBy([], ['id' => 'DESC']),
            'tools' => $toolRepository->findBy([], ['id' => 'DESC']),
            'tool' => $tool,
            'session' => $this->getUser()->getId(),
            'category_name' => 'all'
        ]);
    }

    /**
     * @Route("/tool/{id}/delete", name="tool_delete")
     */
    public function delete(Tool $tool, EntityManagerInterface $em, Functions $functions)
    {   
        $count = $tool->lengthUser();
        $user = $this->getUser();
        $role = $user->getRoles()[0];
        if($role == "ROLE_ADMIN"){
            $oldImage = $tool->getImage();
            $functions->deleteImage($oldImage);
            $em->remove($tool);
            $em->flush();
            return $this->redirectToRoute('tool');
        }
        else{
            if($count > 1){
                $tool->removeUser($user);
                $em->flush();
                return $this->redirectToRoute('tool');
            }
            else{
                $oldImage = $tool->getImage();
                $functions->deleteImage($oldImage);
                $em->remove($tool);
                $em->flush();
                return $this->redirectToRoute('tool');
            }
        }
    }     
}
