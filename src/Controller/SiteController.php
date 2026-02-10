<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Site;
use App\Form\SiteType;
use App\Repository\SiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SiteController extends AbstractController
{
    #[Route('/campus/ajouter', name: 'site_ajouter', methods: ['POST', 'GET'])]
    public function ajouter(Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Vous n\'avez pas l\'autorisation pour ajouter un campus.');

            return $this->redirectToRoute('main_index');
        }
        $site = new Site();

        $siteForm = $this->createForm(SiteType::class, $site);

        if ($siteForm->isSubmitted() && $siteForm->isValid()) {



            $siteForm->handleRequest($request);

            $em->persist($site);

            $em->flush();
            $this->addFlash("success", "Le campus a bien été créé.");
            return $this->redirectToRoute('admin_sites');
        }
        return $this->render('site/ajouter.html.twig', ['siteForm' => $siteForm]);
    }

    #[Route('/campus/liste', name: 'site_list', methods: ['GET', 'POST'])]
    public function list(SiteRepository $siteRepository, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Vous n\'avez pas l\'autorisation necessaire pour voir cette page.');

            return $this->redirectToRoute('main_index');
        }
        $sites = $siteRepository->findBy([], ['nom' => 'ASC']);

        if($request->query->count() !== 0){

            $searchText = $request->query->get('search');

            $qb = $siteRepository->createQueryBuilder('s')
                ->andWhere('LOWER(s.nom) LIKE LOWER(:searchText)')
                ->setParameter('searchText', '%' . $searchText . '%');

            $sites = $qb->getQuery()->getResult();
        }
        $site = new Site();

        $siteForm = $this->createForm(SiteType::class, $site);
        $siteForm->handleRequest($request);

        if ($siteForm->isSubmitted() && $siteForm->isValid()) {

            $em->persist($site);
            $em->flush();
            $this->addFlash("success", "Le campus a bien été créé.");
            return $this->redirectToRoute('site_list');
        }

        return $this->render('site/list.html.twig', ['sites' => $sites, 'siteForm' => $siteForm]);
    }

    #[Route('/campus/{id}/supprimer', name: 'site_delete', methods: ['POST'])]
    public function delete(Site $site, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Vous n\'avez pas l\'autorisation necessaire pour supprimer ce campus.');

            return $this->redirectToRoute('main_index');
        }

        if ($this->isCsrfTokenValid('delete_site_' . $site->getId(), $request->request->get('_token'))) {
            if (count($site->getSorties()) > 0) {
                foreach ($site->getSorties() as $sortie) {
                    $sortieListeNoms = '';
                    $sortieListeNoms .= '<br> - ' . $sortie->getNom();
                }
                $this->addFlash('error', 'Impossible de supprimer ce campus car il est lié à des sorties.' . $sortieListeNoms);
                return $this->redirectToRoute('site_list');
            }else{
                $em->remove($site);
                $em->flush();

                $this->addFlash('success', 'Le campus a bien été supprimé.');
            }
        }

        return $this->redirectToRoute('site_list');

    }

    #[Route('/site/{id}/modifier', name: 'site_edit', methods: ['POST', 'GET'])]
    public function edit(Request $request, EntityManagerInterface $em, Site $site): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Vous n\'avez pas l\'autorisation necessaire pour modifier ce campus.');

            return $this->redirectToRoute('main_index');
        }

        $siteForm = $this->createForm(SiteType::class, $site);

        $siteForm->handleRequest($request);

        if($siteForm->isSubmitted() && $siteForm->isValid()) {

            $em->flush();

            $this->addFlash("success", "Le site a bien été modifié.");
            return $this->redirectToRoute('site_list');
        }

        return $this->render('site/edit.html.twig', ['siteForm' => $siteForm, 'site' => $site]);
    }
}



