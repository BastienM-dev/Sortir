<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainController extends AbstractController
{
    #[Route('/index', name: 'main_index')]
    public function index(SortieRepository $sortieRepository): Response
    {

        //redirection vers la vraie page d'accueil pour ne pas casser les redirections vers main_index
        return $this->redirectToRoute('sortie_list');

    }
}
