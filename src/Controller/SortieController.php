<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SortieController extends AbstractController
{
    #[Route('/sortie/creer', name: 'sortie_create', methods: ['GET', 'POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        EtatRepository $etatRepository
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var Participant $user */
        $user = $this->getUser();
        if (!$user instanceof Participant) {
            throw $this->createAccessDeniedException("Utilisateur invalide.");
        }

        $sortie = new Sortie();

        // Champs automatiques
        $sortie->setOrganisateur($user);
        $sortie->setSite($user->getSite());

        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Récupération des états (via libelle dans Etat.php)
            $etatEnCreation = $etatRepository->findOneBy(['libelle' => 'En création']);
            $etatOuverte    = $etatRepository->findOneBy(['libelle' => 'Ouverte']);

            if (!$etatEnCreation || !$etatOuverte) {
                throw $this->createNotFoundException("Les états 'En création' et/ou 'Ouverte' n'existent pas en base.");
            }

            // Quel bouton a été cliqué ?
            if ($form->get('publish')->isClicked()) {
                $sortie->setEtat($etatOuverte);
                $this->addFlash('success', 'Sortie publiée ✅');
            } else {
                $sortie->setEtat($etatEnCreation);
                $this->addFlash('success', 'Sortie enregistrée (En création) ✅');
            }

            $em->persist($sortie);
            $em->flush();

            // La redirection pointe temporairement vers "main_home".
            // Elle devra être remplacée par la route de la liste des sorties
            // lors de l’implémentation des tâches d’affichage (Tâche 4 & 5).
            return $this->redirectToRoute('main_home');
        }

        return $this->render('sortie/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
