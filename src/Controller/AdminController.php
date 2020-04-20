<?php

namespace App\Controller;

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




class AdminController extends AbstractController
{
    /**
     * Lister les utilisateurs.
     *
     * @Route("/admin", name="admin_index")
     */

    public function index(UserRepository $userRepository, categoryRepository $categoryRepository)
    {
        return $this->render('home/index.html.twig', [
        ]);
    }
}