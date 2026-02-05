<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Inscription;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SortieController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    // ==========================================
    // PARTIE KEVIN - Liste avec filtres
    // ==========================================

    #[Route('/', name: 'sortie_list', methods: ['GET'])]
    public function list(
        SortieRepository $sortieRepository,
        SiteRepository $siteRepository,
        Request $request
    ): Response {
        /** @var Participant $user */
        $user = $this->getUser();

        $siteList = $siteRepository->findBy([], ['nom' => 'ASC']);
        $siteId = $request->query->get('campus');
        $searchText = $request->query->get('search');
        $startDate = $request->query->get('date_from');
        $endDate = $request->query->get('date_to');
        $organisateur = $request->query->get('organisateur');
        $inscrit = $request->query->get('inscrit');
        $nonInscrit = $request->query->get('non_inscrit');
        $terminees = $request->query->get('terminees');

        $qb = $sortieRepository->createQueryBuilder('s');

        if ($siteId) {
            $qb->andWhere('s.site = :siteId')
                ->setParameter('siteId', $siteId);
        }

        if ($startDate) {
            $startDate = new \DateTimeImmutable($startDate);
            $qb->andWhere('s.dateHeureDebut > :startDate')
                ->setParameter('startDate', $startDate);
        }

        if ($endDate) {
            $endDate = new \DateTimeImmutable($endDate);
            $qb->andWhere('s.dateLimiteInscription < :endDate')
                ->setParameter('endDate', $endDate);
        }

        if ($organisateur) {
            $qb->andWhere('s.organisateur = :user')
                ->setParameter('user', $user);
        }

        if ($inscrit) {
            $qb->join('s.inscriptions', 'i')
                ->andWhere('i.participant = :user')
                ->setParameter('user', $user);
        }

        if ($nonInscrit) {
            $qb->leftJoin('s.inscriptions', 'i2', 'WITH', 'i2.participant = :user')
                ->andWhere('i2.id IS NULL')
                ->setParameter('user', $user);
        }

        $sortieList = $qb->getQuery()->getResult();

        return $this->render('sortie/list.html.twig', [
            'sortieList' => $sortieList,
            'siteList' => $siteList
        ]);
    }

    // ==========================================
    // PARTIE JUSTINE - Création/Modification
    // ==========================================

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
            $etatEnCreation = $etatRepository->findOneBy(['libelle' => 'En création']);
            $etatOuverte    = $etatRepository->findOneBy(['libelle' => 'Ouverte']);

            if (!$etatEnCreation || !$etatOuverte) {
                throw $this->createNotFoundException(
                    "Les états 'En création' et/ou 'Ouverte' n'existent pas en base."
                );
            }

            if ($form->get('publish')->isClicked()) {
                $sortie->setEtat($etatOuverte);
                $this->addFlash('success', 'Sortie publiée ✅');
            } else {
                $sortie->setEtat($etatEnCreation);
                $this->addFlash('success', 'Sortie enregistrée (En création) ✅');
            }

            $em->persist($sortie);
            $em->flush();

            return $this->redirectToRoute('sortie_list');
        }

        return $this->render('sortie/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/sortie/{id}/modifier', name: 'sortie_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(
        Sortie $sortie,
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

        // Vérif organisateur
        if ($sortie->getOrganisateur()?->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException(
                "Tu n'es pas l'organisateur de cette sortie."
            );
        }

        // Vérif état : uniquement "En création"
        $etat = $sortie->getEtat();
        $libelleEtat = $etat?->getLibelle();

        if ($libelleEtat !== 'En création') {
            $this->addFlash(
                'error',
                "Impossible de modifier : la sortie n'est plus en création."
            );
            return $this->redirectToRoute('sortie_list');
        }

        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $etatEnCreation = $etatRepository->findOneBy(['libelle' => 'En création']);
            $etatOuverte    = $etatRepository->findOneBy(['libelle' => 'Ouverte']);

            if (!$etatEnCreation || !$etatOuverte) {
                throw $this->createNotFoundException(
                    "Les états 'En création' et/ou 'Ouverte' n'existent pas en base."
                );
            }

            if ($form->get('publish')->isClicked()) {
                $sortie->setEtat($etatOuverte);
                $this->addFlash('success', 'Sortie publiée ✅');
            } else {
                $sortie->setEtat($etatEnCreation);
                $this->addFlash('success', 'Sortie modifiée ✅');
            }

            $em->flush();

            return $this->redirectToRoute('sortie_list');
        }

        return $this->render('sortie/edit.html.twig', [
            'form' => $form->createView(),
            'sortie' => $sortie,
        ]);
    }

    // ==========================================
    // PARTIE BASTIEN - Inscriptions/Annulation
    // ==========================================

    #[Route('/sortie/{id}/inscrire', name: 'sortie_inscrire', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function inscrire(
        Sortie $sortie,
        EntityManagerInterface $em,
        EtatRepository $etatRepository
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $participant = $this->getUser();

        if ($sortie->getOrganisateur() === $participant) {
            $this->addFlash('error', 'Vous ne pouvez pas vous inscrire à votre propre sortie.');
            return $this->redirectToRoute('sortie_list');
        }

        if ($sortie->getEtat()->getLibelle() !== 'Ouverte') {
            $this->addFlash('error', 'Cette sortie n\'est plus ouverte aux inscriptions.');
            return $this->redirectToRoute('sortie_list');
        }

        $now = new \DateTimeImmutable();
        if ($now > $sortie->getDateLimiteInscription()) {
            $this->addFlash('error', 'La date limite d\'inscription est dépassée.');
            return $this->redirectToRoute('sortie_list');
        }

        if ($sortie->getNbInscrits() >= $sortie->getNbInscriptionsMax()) {
            $this->addFlash('error', 'Il n\'y a plus de place disponible pour cette sortie.');
            return $this->redirectToRoute('sortie_list');
        }

        if ($sortie->isParticipantInscrit($participant)) {
            $this->addFlash('warning', 'Vous êtes déjà inscrit à cette sortie.');
            return $this->redirectToRoute('sortie_list');
        }

        $inscription = new Inscription();
        $inscription->setParticipant($participant);
        $inscription->setSortie($sortie);

        $em->persist($inscription);

        if ($sortie->getNbInscrits() + 1 >= $sortie->getNbInscriptionsMax()) {
            $etatCloturee = $etatRepository->findOneBy(['libelle' => 'Clôturée']);
            if ($etatCloturee) {
                $sortie->setEtat($etatCloturee);
            }
        }

        $em->flush();

        $this->addFlash('success', 'Vous êtes maintenant inscrit à la sortie "' . $sortie->getNom() . '".');

        return $this->redirectToRoute('sortie_list');
    }

    #[Route('/sortie/{id}/desister', name: 'sortie_desister', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function desister(
        Sortie $sortie,
        EntityManagerInterface $em,
        EtatRepository $etatRepository
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $participant = $this->getUser();

        if (!$sortie->isParticipantInscrit($participant)) {
            $this->addFlash('error', 'Vous n\'êtes pas inscrit à cette sortie.');
            return $this->redirectToRoute('sortie_list');
        }

        $now = new \DateTimeImmutable();
        if ($now >= $sortie->getDateHeureDebut()) {
            $this->addFlash('error', 'Impossible de se désister, la sortie a déjà commencé.');
            return $this->redirectToRoute('sortie_list');
        }

        foreach ($sortie->getInscriptions() as $inscription) {
            if ($inscription->getParticipant() === $participant) {
                $sortie->removeInscription($inscription);
                $em->remove($inscription);
                break;
            }
        }

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

        return $this->redirectToRoute('sortie_list');
    }

    #[Route('/sortie/{id}/annuler', name: 'sortie_annuler', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function annuler(
        Sortie $sortie,
        EntityManagerInterface $em,
        EtatRepository $etatRepository,
        Request $request
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $participant = $this->getUser();

        if ($sortie->getOrganisateur() !== $participant) {
            $this->addFlash('error', 'Seul l\'organisateur peut annuler cette sortie.');
            return $this->redirectToRoute('sortie_list');
        }

        $etatActuel = $sortie->getEtat()->getLibelle();
        if ($etatActuel !== 'Ouverte' && $etatActuel !== 'Clôturée') {
            $this->addFlash('error', 'Cette sortie ne peut pas être annulée dans son état actuel.');
            return $this->redirectToRoute('sortie_list');
        }

        $now = new \DateTimeImmutable();
        if ($now >= $sortie->getDateHeureDebut()) {
            $this->addFlash('error', 'Impossible d\'annuler une sortie qui a déjà commencé.');
            return $this->redirectToRoute('sortie_list');
        }

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

            return $this->redirectToRoute('sortie_list');
        }

        return $this->render('sortie/annuler.html.twig', [
            'sortie' => $sortie,
        ]);
    }
}