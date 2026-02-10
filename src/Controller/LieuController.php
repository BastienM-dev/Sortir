<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Lieu;
use App\Form\LieuType;
use App\Repository\LieuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/lieu', name: 'lieu_')]
class LieuController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    /**
     * Liste tous les lieux
     */
    #[Route('/', name: 'list', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function list(LieuRepository $lieuRepository): Response
    {
        $lieux = $lieuRepository->findAll();

        return $this->render('lieu/list.html.twig', [
            'lieux' => $lieux,
        ]);
    }

    /**
     * Créer un nouveau lieu
     */
    #[Route('/creer', name: 'create', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request): Response
    {
        $lieu = new Lieu();
        $form = $this->createForm(LieuType::class, $lieu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($lieu);
            $this->em->flush();

            $this->addFlash('success', 'Le lieu "' . $lieu->getNom() . '" a été créé avec succès.');

            return $this->redirectToRoute('lieu_list');
        }

        return $this->render('lieu/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Modifier un lieu existant
     */
    #[Route('/{id}/modifier', name: 'edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Lieu $lieu, Request $request): Response
    {
        $form = $this->createForm(LieuType::class, $lieu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            $this->addFlash('success', 'Le lieu "' . $lieu->getNom() . '" a été modifié avec succès.');

            return $this->redirectToRoute('lieu_list');
        }

        return $this->render('lieu/edit.html.twig', [
            'form' => $form->createView(),
            'lieu' => $lieu,
        ]);
    }

    /**
     * Supprimer un lieu
     */
    #[Route('/{id}/supprimer', name: 'delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Lieu $lieu): Response
    {
        // Vérifier si le lieu est utilisé par des sorties
        if ($lieu->getSorties()->count() > 0) {
            $this->addFlash('error', 'Impossible de supprimer ce lieu car il est utilisé par des sorties.');
            return $this->redirectToRoute('lieu_list');
        }

        $nom = $lieu->getNom();
        $this->em->remove($lieu);
        $this->em->flush();

        $this->addFlash('success', 'Le lieu "' . $nom . '" a été supprimé avec succès.');

        return $this->redirectToRoute('lieu_list');
    }
}