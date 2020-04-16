<?php

namespace App\Controller;

use App\Form\UserType;
use App\Entity\User;
use App\Entity\Image;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Cocur\Slugify\Slugify;



class UserController extends AbstractController
{
    /**
     * Lister les utilisateurs.
     *
     * @Route("/user/show", name="user_show")
     */
    public function index(UserRepository $userRepository, categoryRepository $categoryRepository)
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findBy([], ['id' => 'DESC']),
            'categories' => $categoryRepository->findBy([], ['id' => 'DESC'])
        ]);
    }
    /**
     * Ajouter un utilisateur
     *
     * @Route("/user/add", name="user_add")
     */
    public function add(Request $request,EntityManagerInterface $em, categoryRepository $categoryRepository)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
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
                $user->setImage($newFilename);
            }
            $user = $form->getData();
            $em->persist($user);
            $em->flush();

            // ... persist the $user variable or any other work

            return $this->redirect($this->generateUrl('index'));
        }

        return $this->render('security/add.html.twig', [
            'form' => $form->createView(),
            'categories' => $categoryRepository->findBy([], ['id' => 'DESC'])
        ]);
    }


    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils, categoryRepository $categoryRepository): Response
    {
        if ($this->getUser()) {
             return $this->redirectToRoute('index');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error, 'categories' => $categoryRepository->findBy([], ['id' => 'DESC'])]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {   
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
