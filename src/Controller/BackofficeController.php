<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class BackofficeController extends AbstractController
{
    /**
     * @Route("/backoffice", name="backoffice_index")
     */
    public function index()
    {
        return $this->render('backoffice/index.html.twig',
        [
        ]
        );
    }
}
