<?php

namespace App\Command;

use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:update-sorties-etat',
    description: 'Met à jour automatiquement les états des sorties en fonction des dates',
)]
class UpdateSortiesEtatCommand extends Command
{
    public function __construct(
        private SortieRepository $sortieRepository,
        private EtatRepository $etatRepository,
        private EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $now = new \DateTimeImmutable();

        $io->title('🔄 Mise à jour automatique des états des sorties');
        $io->text('Date/heure actuelle : ' . $now->format('d/m/Y H:i:s'));

        $totalUpdated = 0;

        // Récupérer les états une seule fois
        $etatOuverte = $this->etatRepository->findOneBy(['libelle' => 'Ouverte']);
        $etatCloturee = $this->etatRepository->findOneBy(['libelle' => 'Clôturée']);
        $etatEnCours = $this->etatRepository->findOneBy(['libelle' => 'En cours']);
        $etatTerminee = $this->etatRepository->findOneBy(['libelle' => 'Terminée']);
        $etatHistorisee = $this->etatRepository->findOneBy(['libelle' => 'Historisée']);

        if (!$etatOuverte || !$etatCloturee || !$etatEnCours || !$etatTerminee || !$etatHistorisee) {
            $io->error('❌ Tous les états nécessaires ne sont pas présents en base de données.');
            return Command::FAILURE;
        }

        // ================================================================
        // 1. OUVERTE → CLÔTURÉE (date limite d'inscription dépassée)
        // ================================================================
        $io->section('📝 Vérification : Ouverte → Clôturée');

        $sortiesOuvertes = $this->sortieRepository->findBy(['etat' => $etatOuverte]);
        $countOuverteToCloturee = 0;

        foreach ($sortiesOuvertes as $sortie) {
            if ($now > $sortie->getDateLimiteInscription()) {
                $sortie->setEtat($etatCloturee);
                $countOuverteToCloturee++;
                $io->text(sprintf(
                    '  ✓ "%s" → Clôturée (date limite : %s)',
                    $sortie->getNom(),
                    $sortie->getDateLimiteInscription()->format('d/m/Y H:i')
                ));
            }
        }

        $io->success(sprintf('%d sortie(s) passée(s) en Clôturée', $countOuverteToCloturee));
        $totalUpdated += $countOuverteToCloturee;

        // ================================================================
        // 2. CLÔTURÉE → EN COURS (date/heure de début dépassée)
        // ================================================================
        $io->section('🏃 Vérification : Clôturée → En cours');

        $sortiesCloturees = $this->sortieRepository->findBy(['etat' => $etatCloturee]);
        $countClotureeToEnCours = 0;

        foreach ($sortiesCloturees as $sortie) {
            if ($now >= $sortie->getDateHeureDebut()) {
                $sortie->setEtat($etatEnCours);
                $countClotureeToEnCours++;
                $io->text(sprintf(
                    '  ✓ "%s" → En cours (début : %s)',
                    $sortie->getNom(),
                    $sortie->getDateHeureDebut()->format('d/m/Y H:i')
                ));
            }
        }

        $io->success(sprintf('%d sortie(s) passée(s) en En cours', $countClotureeToEnCours));
        $totalUpdated += $countClotureeToEnCours;

        // ================================================================
        // 3. EN COURS → TERMINÉE (date de fin dépassée)
        // ================================================================
        $io->section('✅ Vérification : En cours → Terminée');

        $sortiesEnCours = $this->sortieRepository->findBy(['etat' => $etatEnCours]);
        $countEnCoursToTerminee = 0;

        foreach ($sortiesEnCours as $sortie) {
            // Calculer la date de fin : dateHeureDebut + duree (en minutes)
            $dateFin = (clone $sortie->getDateHeureDebut())->modify('+' . $sortie->getDuree() . ' minutes');

            if ($now >= $dateFin) {
                $sortie->setEtat($etatTerminee);
                $countEnCoursToTerminee++;
                $io->text(sprintf(
                    '  ✓ "%s" → Terminée (fin : %s)',
                    $sortie->getNom(),
                    $dateFin->format('d/m/Y H:i')
                ));
            }
        }

        $io->success(sprintf('%d sortie(s) passée(s) en Terminée', $countEnCoursToTerminee));
        $totalUpdated += $countEnCoursToTerminee;

        // ================================================================
        // 4. TERMINÉE → HISTORISÉE (1 mois après la fin)
        // ================================================================
        $io->section('📦 Vérification : Terminée → Historisée');

        $sortiesTerminees = $this->sortieRepository->findBy(['etat' => $etatTerminee]);
        $countTermineeToHistorisee = 0;

        foreach ($sortiesTerminees as $sortie) {
            // Calculer la date de fin
            $dateFin = (clone $sortie->getDateHeureDebut())->modify('+' . $sortie->getDuree() . ' minutes');

            // Ajouter 1 mois
            $dateArchivage = (clone $dateFin)->modify('+1 month');

            if ($now >= $dateArchivage) {
                $sortie->setEtat($etatHistorisee);
                $countTermineeToHistorisee++;
                $io->text(sprintf(
                    '  ✓ "%s" → Historisée (archivage : %s)',
                    $sortie->getNom(),
                    $dateArchivage->format('d/m/Y H:i')
                ));
            }
        }

        $io->success(sprintf('%d sortie(s) passée(s) en Historisée', $countTermineeToHistorisee));
        $totalUpdated += $countTermineeToHistorisee;

        // ================================================================
        // SAUVEGARDE EN BASE
        // ================================================================
        if ($totalUpdated > 0) {
            $this->em->flush();
            $io->newLine();
            $io->success(sprintf(
                '✅ %d sortie(s) mise(s) à jour au total !',
                $totalUpdated
            ));
        } else {
            $io->newLine();
            $io->info('ℹ️  Aucune sortie à mettre à jour.');
        }

        return Command::SUCCESS;
    }
}