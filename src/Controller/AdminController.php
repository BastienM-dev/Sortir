<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminController extends AbstractController
{
    #[Route('/administration', name: 'admin_dashboard')]
    public function dashboard(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }


    #[Route('/administration/campus', name: 'admin_campus')]
    public function campus(): Response
    {
        return $this->render('admin/campus.html.twig');
    }

    #[Route('/administration/villes', name: 'admin_villes')]
    public function villes(): Response
    {
        return $this->render('admin/villes.html.twig');
    }

    #[Route('/administration/utilisateurs', name: 'admin_utilisateurs')]
    public function utilisateurs(): Response
    {
        return $this->render('admin/utilisateurs.html.twig');
    }

}
