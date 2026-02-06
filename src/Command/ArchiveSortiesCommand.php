<?php

namespace App\Command;

use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:archive-sorties',
    description: 'Archive les sorties réalisées depuis plus d’un mois (etat -> Historisée).',
)]
class ArchiveSortiesCommand extends Command
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
        $etatHistorisee = $this->etatRepository->findOneBy(['libelle' => 'Historisée']);
        if (!$etatHistorisee) {
            $output->writeln('<error>Etat "Historisée" introuvable en base.</error>');
            return Command::FAILURE;
        }

        $limit = new \DateTimeImmutable('-1 month');

        // À créer/corriger dans SortieRepository
        $sorties = $this->sortieRepository->findToArchive($limit);

        foreach ($sorties as $sortie) {
            // ✅ chez toi c’est "etat" (singulier)
            $sortie->setEtat($etatHistorisee);
        }

        $this->em->flush();

        $output->writeln(sprintf('<info>%d sortie(s) historisée(s).</info>', count($sorties)));
        return Command::SUCCESS;
    }
}
