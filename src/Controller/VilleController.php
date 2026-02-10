<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Ville;
use App\Form\VilleType;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class VilleController extends AbstractController
{


    #[Route('/ville/liste', name: 'ville_list')]
    public function liste(VilleRepository $villeRepository, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Vous n\'avez pas l\'autorisation necessaire pour voir cette page.');

            return $this->redirectToRoute('main_index');
        }

        $villes = $villeRepository->findBy([], ['nom' => 'ASC']);

        if($request->query->count() !== 0){

            $searchText = $request->query->get('search');

            $qb = $villeRepository->createQueryBuilder('v')
                ->andWhere('LOWER(v.nom) LIKE LOWER(:searchText)')
                ->setParameter('searchText', '%' . $searchText . '%');

            $villes = $qb->getQuery()->getResult();
        }

        $ville = new Ville();

        $villeForm = $this->createForm(VilleType::class, $ville);

        $villeForm->handleRequest($request);

        if($villeForm->isSubmitted() && $villeForm->isValid()) {
            $em->persist($ville);
            $em->flush();

            $this->addFlash("success", "La ville a bien été créée.");
            return $this->redirectToRoute('ville_list');
        }


        return $this->render('ville/list.html.twig', ['villes' => $villes, 'villeForm' => $villeForm]);
    }

    #[Route('/ville/{id}/supprimer', name: 'ville_delete', methods: ['POST'])]
    public function delete(Ville $ville, Request $request, EntityManagerInterface $em): Response
    {

        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Vous n\'avez pas l\'autorisation de supprimer une ville.');

            return $this->redirectToRoute('main_index');
        }

        if ($this->isCsrfTokenValid('delete_ville_' . $ville->getId(), $request->request->get('_token'))) {
            if (count($ville->getLieux()) > 0) {
                $this->addFlash('error', 'Impossible de supprimer cette ville car elle contient des lieux.');
                return $this->redirectToRoute('ville_list');
            }else{
                $em->remove($ville);
                $em->flush();

                $this->addFlash('success', 'La ville a bien été supprimée.');
            }

        }

        return $this->redirectToRoute('ville_list');

    }


        #[Route('/ville/ajouter', name: 'ville_ajouter', methods: ['POST', 'GET'])]
    public function ajouter(Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Vous n\'avez pas l\'autorisation necessaire pour ajouter une ville.');

            return $this->redirectToRoute('main_index');
        }

        $ville = new Ville();

        $villeForm = $this->createForm(VilleType::class, $ville);

        $villeForm->handleRequest($request);

        if($villeForm->isSubmitted() && $villeForm->isValid()) {
            $em->persist($ville);
            $em->flush();

            $this->addFlash("success", "La ville a bien été créée.");
            return $this->redirectToRoute('ville_list');
        }

        return $this->render('ville/ajouter.html.twig', ['villeForm' => $villeForm]);
    }

    #[Route('/ville/{id}/modifier', name: 'ville_edit', methods: ['POST', 'GET'])]
    public function edit(Request $request, EntityManagerInterface $em, Ville $ville): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Vous n\'avez pas l\'autorisation necessaire pour modifier une ville.');

            return $this->redirectToRoute('main_index');
        }
        $villeForm = $this->createForm(VilleType::class, $ville);

        $villeForm->handleRequest($request);

        if($villeForm->isSubmitted() && $villeForm->isValid()) {

            $em->flush();

            $this->addFlash("success", "La ville a bien été modifiée.");
            return $this->redirectToRoute('ville_list');
        }

        return $this->render('ville/edit.html.twig', ['villeForm' => $villeForm, 'ville' => $ville]);
    }

}

