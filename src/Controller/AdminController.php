<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ParticipantRepository;
use App\Repository\SiteRepository;
use App\Repository\VilleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminController extends AbstractController
{
    #[Route('/administration', name: 'admin_dashboard')]
    public function dashboard(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }


    #[Route('/administration/campus', name: 'admin_campus', methods: ['GET'])]
    public function campus(SiteRepository $siteRepository, Request $request): Response
    {
        $sites = $siteRepository->findBy([], ['nom' => 'ASC']);

        if($request->query->count() !== 0){

            $searchText = $request->query->get('search');

            $qb = $siteRepository->createQueryBuilder('s')
                ->andWhere('LOWER(s.nom) LIKE LOWER(:searchText)')
                ->setParameter('searchText', '%' . $searchText . '%');

            $sites = $qb->getQuery()->getResult();
        }

        return $this->render('admin/campus.html.twig', ['sites' => $sites]);
    }

    #[Route('/administration/villes', name: 'admin_villes')]
    public function villes(VilleRepository $villeRepository, Request $request): Response
    {
        $villes = $villeRepository->findBy([], ['nom' => 'ASC']);

        if($request->query->count() !== 0){

            $searchText = $request->query->get('search');

            $qb = $villeRepository->createQueryBuilder('v')
                ->andWhere('LOWER(v.nom) LIKE LOWER(:searchText)')
                ->setParameter('searchText', '%' . $searchText . '%');

            $villes = $qb->getQuery()->getResult();
        }

        return $this->render('admin/villes.html.twig', ['villes' => $villes]);
    }


    #[Route('/administration/utilisateurs', name: 'admin_utilisateurs')]
    public function utilisateurs(ParticipantRepository $participantRepository): Response
    {
        $participants = $participantRepository->findBy([], ['nom' => 'ASC']);
        return $this->render('admin/utilisateurs.html.twig', ['participants' => $participants]);
    }



}
