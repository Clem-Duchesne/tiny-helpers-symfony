<?php

namespace App\Controller;

use App\Service\Functions;
use App\Form\UserType;
use App\Entity\User;
use App\Entity\Image;
use App\Entity\Category;
use App\Entity\Tool;
use App\Repository\ToolRepository;
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
     * @Route("/users", name="user")
     */
    public function index(UserRepository $userRepository, categoryRepository $categoryRepository, toolRepository $toolRepository)
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findBy([], ['id' => 'DESC']),
            'categories' => $categoryRepository->findBy([], ['id' => 'DESC']),
            'tools' => $toolRepository->findBy([], ['id' => 'DESC']),
            'category_name' => 'all'
        ]);
    }
    /**
     * Ajouter un utilisateur
     *
     * @Route("/user/add", name="user_add")
     */
    public function add(Request $request,EntityManagerInterface $em, categoryRepository $categoryRepository, toolRepository $toolRepository, Functions $functions, userRepository $userRepository)
    {
        $user = new User();
        $message = '';
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('file')->getData();
            $username = $form->get('username')->getData();
            
            if($userRepository->findOneBy(['username' => $user])){
                $message = "Ce nom d'utilisateur existe déjà";
                return $this->render('security/add.html.twig', [
                    'form' => $form->createView(),
                    'categories' => $categoryRepository->findBy([], ['id' => 'DESC']),
                    'tools' => $toolRepository->findBy([], ['id' => 'DESC']),
                    'message' => $message,
                    'category_name' => 'all'
                ]);
            }
            else{
                 // this condition is needed because the 'brochure' field is not required
                // so the PDF file must be processed only when a file is uploade
                if ($imageFile) {
                    $uploadDir = $this->getParameter('upload_dir');
                    // updates the 'brochureFilename' property to store the PDF file name
                    // instead of its contents
                    $user->setImage($functions->addImage($imageFile, $uploadDir));
                }
                $password = $form->get('password')->getData();
                $user->setPassword($functions->encrypt($password));
                $user->setRoles(['ROLE_USER']);
                $user = $form->getData();
                $em->persist($user);
                $em->flush();

                // ... persist the $user variable or any other work

                return $this->redirect($this->generateUrl('index'));
            }
        }
        return $this->render('security/add.html.twig', [
            'form' => $form->createView(),
            'categories' => $categoryRepository->findBy([], ['id' => 'DESC']),
            'tools' => $toolRepository->findBy([], ['id' => 'DESC']),
            'message' => $message,
            'category_name' => 'all'
        ]);
    }

    /**
     * Modifier le profil utilisateur.
     *
     * @Route("/user/{id}/edit", name="user_edit", methods={"GET","POST"})
     */
    public function update(Request $request, EntityManagerInterface $em, User $user,  categoryRepository $categoryRepository, toolRepository $toolRepository, Functions $functions) : Response
    {   
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('file')->getData();
            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            
            if ($imageFile) {
                $uploadDir = $this->getParameter('upload_dir');
                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents 
                $oldImage = $user->getImage();
                $user->setImage($functions->addImage($imageFile, $uploadDir));
                $functions->deleteImage($oldImage);
            }
           
            $password = $form->get('password')->getData();
            if(strpos($password, '$2y') !== false){
                $user->setPassword($password);
            }
            else{
                $user->setPassword($functions->encrypt($password));  
            }
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('tool');
            
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
            'categories' => $categoryRepository->findBy([], ['id' => 'DESC']),
            'tools' => $toolRepository->findBy([], ['id' => 'DESC']),
            'user' => $user,
            'category_name' => 'all'
        ]);
    }


    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils, categoryRepository $categoryRepository, toolRepository $toolRepository, Functions $functions): Response
    {
        
        if ($this->getUser()) {
             return $this->redirectToRoute('index');
        }
        
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error, 'categories' => $categoryRepository->findBy([], ['id' => 'DESC']),  'tools' => $toolRepository->findBy([], ['id' => 'DESC']), 'category_name' => 'all']);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {   
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
