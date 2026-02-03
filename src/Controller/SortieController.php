<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Inscription;
use App\Entity\Sortie;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sortie', name: 'sortie_')]
class SortieController extends AbstractController
{
    /**
     * Inscription à une sortie
     */
    #[Route('/{id}/inscrire', name: 'inscrire', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function inscrire(
        Sortie $sortie,
        EntityManagerInterface $em,
        EtatRepository $etatRepository
    ): Response {
        // 1. Vérifier que l'utilisateur est connecté
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $participant = $this->getUser();

        // 2. RÈGLE : L'organisateur ne peut pas s'inscrire à sa propre sortie
        if ($sortie->getOrganisateur() === $participant) {
            $this->addFlash('error', 'Vous ne pouvez pas vous inscrire à votre propre sortie.');
            return $this->redirectToRoute('main_index');
        }

        // 3. RÈGLE : La sortie doit être en état "Ouverte"
        if ($sortie->getEtat()->getLibelle() !== 'Ouverte') {
            $this->addFlash('error', 'Cette sortie n\'est plus ouverte aux inscriptions.');
            return $this->redirectToRoute('main_index');
        }

        // 4. RÈGLE : La date limite d'inscription ne doit pas être dépassée
        $now = new \DateTimeImmutable();
        if ($now > $sortie->getDateLimiteInscription()) {
            $this->addFlash('error', 'La date limite d\'inscription est dépassée.');
            return $this->redirectToRoute('main_index');
        }

        // 5. RÈGLE : Il doit rester des places disponibles
        if ($sortie->getNbInscrits() >= $sortie->getNbInscriptionsMax()) {
            $this->addFlash('error', 'Il n\'y a plus de place disponible pour cette sortie.');
            return $this->redirectToRoute('main_index');
        }

        // 6. RÈGLE : Le participant ne doit pas déjà être inscrit
        if ($sortie->isParticipantInscrit($participant)) {
            $this->addFlash('warning', 'Vous êtes déjà inscrit à cette sortie.');
            return $this->redirectToRoute('main_index');
        }

        // 7. Créer l'inscription
        $inscription = new Inscription();
        $inscription->setParticipant($participant);
        $inscription->setSortie($sortie);
        // La dateInscription est automatiquement définie dans le constructeur

        $em->persist($inscription);

        // 8. Vérifier si la sortie doit passer en état "Clôturée"
        // Après cette inscription, si on atteint le nombre max
        if ($sortie->getNbInscrits() + 1 >= $sortie->getNbInscriptionsMax()) {
            $etatCloturee = $etatRepository->findOneBy(['libelle' => 'Clôturée']);
            if ($etatCloturee) {
                $sortie->setEtat($etatCloturee);
            }
        }

        $em->flush();

        $this->addFlash('success', 'Vous êtes maintenant inscrit à la sortie "' . $sortie->getNom() . '".');

        return $this->redirectToRoute('main_index');
    }

    /**
     * Désistement d'une sortie
     */
    #[Route('/{id}/desister', name: 'desister', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function desister(
        Sortie $sortie,
        EntityManagerInterface $em,
        EtatRepository $etatRepository
    ): Response {
        // 1. Vérifier que l'utilisateur est connecté
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $participant = $this->getUser();

        // 2. RÈGLE : Le participant doit être inscrit
        if (!$sortie->isParticipantInscrit($participant)) {
            $this->addFlash('error', 'Vous n\'êtes pas inscrit à cette sortie.');
            return $this->redirectToRoute('main_index');
        }

        // 3. RÈGLE : La sortie ne doit pas avoir débuté
        $now = new \DateTimeImmutable();
        if ($now >= $sortie->getDateHeureDebut()) {
            $this->addFlash('error', 'Impossible de se désister, la sortie a déjà commencé.');
            return $this->redirectToRoute('main_index');
        }

        // 4. Trouver et supprimer l'inscription
        foreach ($sortie->getInscriptions() as $inscription) {
            if ($inscription->getParticipant() === $participant) {
                $sortie->removeInscription($inscription);
                $em->remove($inscription);
                break;
            }
        }

        // 5. Vérifier si la sortie doit repasser en état "Ouverte"
        // Si elle était clôturée et qu'il y a maintenant des places + date limite OK
        if ($sortie->getEtat()->getLibelle() === 'Clôturée') {
            if ($sortie->getNbInscrits() - 1 < $sortie->getNbInscriptionsMax()
                && $now <= $sortie->getDateLimiteInscription()) {
                $etatOuverte = $etatRepository->findOneBy(['libelle' => 'Ouverte']);
                if ($etatOuverte) {
                    $sortie->setEtat($etatOuverte);
                }
            }
        }

        $em->flush();

        $this->addFlash('success', 'Vous vous êtes désisté de la sortie "' . $sortie->getNom() . '".');

        return $this->redirectToRoute('main_index');
    }

    /**
     * Annuler une sortie
     */
    #[Route('/{id}/annuler', name: 'annuler', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function annuler(
        Sortie $sortie,
        EntityManagerInterface $em,
        EtatRepository $etatRepository,
        Request $request
    ): Response {
        // 1. Vérifier que l'utilisateur est connecté
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $participant = $this->getUser();

        // 2. RÈGLE : Seul l'organisateur peut annuler (pour l'instant, admin dans itération 2)
        if ($sortie->getOrganisateur() !== $participant) {
            $this->addFlash('error', 'Seul l\'organisateur peut annuler cette sortie.');
            return $this->redirectToRoute('main_index');
        }

        // 3. RÈGLE : La sortie doit être publiée (Ouverte ou Clôturée)
        $etatActuel = $sortie->getEtat()->getLibelle();
        if ($etatActuel !== 'Ouverte' && $etatActuel !== 'Clôturée') {
            $this->addFlash('error', 'Cette sortie ne peut pas être annulée dans son état actuel.');
            return $this->redirectToRoute('main_index');
        }

        // 4. RÈGLE : La sortie ne doit pas avoir commencé
        $now = new \DateTimeImmutable();
        if ($now >= $sortie->getDateHeureDebut()) {
            $this->addFlash('error', 'Impossible d\'annuler une sortie qui a déjà commencé.');
            return $this->redirectToRoute('main_index');
        }

        // 5. Si POST : traiter l'annulation
        if ($request->isMethod('POST')) {
            $motif = $request->request->get('motif', '');

            if (empty(trim($motif))) {
                $this->addFlash('error', 'Le motif d\'annulation est obligatoire.');
                return $this->render('sortie/annuler.html.twig', [
                    'sortie' => $sortie,
                ]);
            }

            $etatAnnulee = $etatRepository->findOneBy(['libelle' => 'Annulée']);
            if ($etatAnnulee) {
                $sortie->setEtat($etatAnnulee);
                $sortie->setMotifAnnulation($motif);

                $em->flush();

                $this->addFlash('success', 'La sortie "' . $sortie->getNom() . '" a été annulée.');
            } else {
                $this->addFlash('error', 'Erreur : état "Annulée" introuvable.');
            }

            return $this->redirectToRoute('main_index');
        }

        // Si GET : afficher le formulaire de confirmation
        return $this->render('sortie/annuler.html.twig', [
            'sortie' => $sortie,
        ]);
    }
}