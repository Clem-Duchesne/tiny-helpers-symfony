<?php

namespace App\Controller;

use App\Form\UserType;
use App\Entity\User;
use App\Entity\Image;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\File\UploadedFile;



class UserController extends AbstractController
{
    /**
     * Ajouter un utilisateur
     *
     * @Route("/user/add", name="user_add")
     */
    public function add(Request $request,EntityManagerInterface $em)
    {
        // préparer un utilisateur
        $user = new User();
        //$image = new Image();

        // préparer l'objet formulaire
        $form = $this->createForm(UserType::class, $user);
        // Récupérer (éventuellement) les données soumises
        $form->handleRequest($request);
        // Si le formulaire a été soumis et que les données sont valides
        if ($form->isSubmitted() && $form->isValid()) {
             
             $image = $form->get('image')->getData();
             if ($image) {
                 $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                 // this is needed to safely include the file name as part of the URL
                 $safeFilename = $slugger->slug($originalFilename);
                 $newFilename = $safeFilename.'-'.uniqid().'.'.$image->guessExtension();
 
                 // Move the file to the directory where brochures are stored
                 try {
                     $brochureFile->move(
                         $this->getParameter('img'),
                         $newFilename
                     );
                 } catch (FileException $e) {
                     return "error";
                 }
 
                 $image->setImage($newFilename);
             }
        $user = $form->getData();
        
        
        $em->persist($user);
        $em->flush();
        // stocker un message flash de succès
        $this->addFlash('info', 'utilisateur ' . $user->getUsername() . ' ajouté');
        // rediriger vers l’accueil
        return $this->redirectToRoute('index');
        }
        // formulaire non valide ou 1er acces : afficher le formulaire
        return $this->render('security/add.html.twig', [
        'form' => $form->createView()
        ]) ;
    }
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
