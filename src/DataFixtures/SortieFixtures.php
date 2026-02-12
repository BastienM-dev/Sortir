<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use App\Entity\Inscription;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Sortie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class SortieFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // ⚠️ Sécurité : évite l'erreur "Data too long for column 'nom'"
        // Mets 30 si ta colonne sorties.nom est VARCHAR(30). Sinon adapte à ta DB.
        $maxNom = 30;
        $cutNom = static fn (string $s): string => mb_substr($s, 0, $maxNom);

        // Récupérer les états
        $etatCreation = $this->getReference(AppFixtures::ETAT_EN_CREATION, Etat::class);
        $etatOuverte  = $this->getReference(AppFixtures::ETAT_OUVERTE, Etat::class);
        $etatCloturee = $this->getReference(AppFixtures::ETAT_CLOTUREE, Etat::class);
        $etatEnCours  = $this->getReference(AppFixtures::ETAT_EN_COURS, Etat::class);
        $etatTerminee = $this->getReference(AppFixtures::ETAT_TERMINEE, Etat::class);
        $etatAnnulee  = $this->getReference(AppFixtures::ETAT_ANNULEE, Etat::class);

        // Récupérer les sites
        $siteSaintHerblain = $this->getReference(SiteFixtures::SITE_SAINT_HERBLAIN, Site::class);
        $siteChartres      = $this->getReference(SiteFixtures::SITE_CHARTRES, Site::class);
        $siteLaRoche       = $this->getReference(SiteFixtures::SITE_LA_ROCHE, Site::class);

        $sites = [$siteSaintHerblain, $siteChartres, $siteLaRoche];

        // --- SORTIES EN CRÉATION (2 sorties) ---
        for ($i = 1; $i <= 10; $i++) {
            $sortie = new Sortie();
            $sortie->setNom($cutNom("Sortie en création #$i"));
            $sortie->setDateHeureDebut($faker->dateTimeBetween('+1 week', '+2 weeks'));
            $sortie->setDuree($faker->numberBetween(60, 240));
            $sortie->setDateLimiteInscription((clone $sortie->getDateHeureDebut())->modify('-3 days'));
            $sortie->setNbInscriptionsMax($faker->numberBetween(5, 20));
            $sortie->setInfosSortie($faker->text(200));

            $lieuNum = $faker->numberBetween(1, 15);
            $sortie->setLieu($this->getReference('lieu_' . $lieuNum, Lieu::class));

            $sortie->setEtat($etatCreation);

            $participantNum = $faker->numberBetween(1, 19);
            $sortie->setOrganisateur($this->getReference('participant_' . $participantNum, Participant::class));

            $sortie->setSite($sites[$i % 3]);

            $manager->persist($sortie);
        }

        // --- SORTIES OUVERTES avec places disponibles (5 sorties) ---
        for ($i = 1; $i <= 30; $i++) {
            $sortie = new Sortie();

            $nom = "Sortie ouverte #$i - " . $faker->randomElement([
                    'Bowling', 'Cinéma', 'Restaurant', 'Karting', 'Escape Game'
                ]);
            $sortie->setNom($cutNom($nom));

            $sortie->setDateHeureDebut($faker->dateTimeBetween('+5 days', '+3 weeks'));
            $sortie->setDuree($faker->numberBetween(90, 180));
            $sortie->setDateLimiteInscription((clone $sortie->getDateHeureDebut())->modify('-2 days'));
            $sortie->setNbInscriptionsMax($faker->numberBetween(8, 15));
            $sortie->setInfosSortie($faker->text(150));

            $lieuNum = $faker->numberBetween(1, 15);
            $sortie->setLieu($this->getReference('lieu_' . $lieuNum, Lieu::class));

            $sortie->setEtat($etatOuverte);

            $participantNum = $faker->numberBetween(1, 19);
            $sortie->setOrganisateur($this->getReference('participant_' . $participantNum, Participant::class));

            $sortie->setSite($sites[$i % 3]);

            $manager->persist($sortie);

            // Ajouter quelques inscriptions
            $nbInscrits = $faker->numberBetween(2, $sortie->getNbInscriptionsMax() - 3);
            for ($j = 0; $j < $nbInscrits; $j++) {
                $inscription = new Inscription();
                $participantNum = (($i * 3 + $j) % 19) + 1;
                $inscription->setParticipant($this->getReference('participant_' . $participantNum, Participant::class));
                $inscription->setSortie($sortie);
                $inscription->setDateInscription($faker->dateTimeBetween('-1 week', 'now'));
                $manager->persist($inscription);
            }

            $this->addReference('sortie_ouverte_' . $i, $sortie);
        }

        // --- SORTIE OUVERTE presque complète ---
        $sortie = new Sortie();
        $sortie->setNom($cutNom("Sortie presque complète - Laser Game"));
        $sortie->setDateHeureDebut($faker->dateTimeBetween('+1 week', '+2 weeks'));
        $sortie->setDuree(120);
        $sortie->setDateLimiteInscription((clone $sortie->getDateHeureDebut())->modify('-2 days'));
        $sortie->setNbInscriptionsMax(5);
        $sortie->setInfosSortie("Attention : il ne reste qu'une place !");
        $sortie->setLieu($this->getReference('lieu_8', Lieu::class));
        $sortie->setEtat($etatOuverte);
        $sortie->setOrganisateur($this->getReference('participant_1', Participant::class));
        $sortie->setSite($siteSaintHerblain);

        $manager->persist($sortie);

        // 4 inscrits sur 5
        for ($j = 2; $j <= 5; $j++) {
            $inscription = new Inscription();
            $inscription->setParticipant($this->getReference('participant_' . $j, Participant::class));
            $inscription->setSortie($sortie);
            $inscription->setDateInscription($faker->dateTimeBetween('-3 days', 'now'));
            $manager->persist($inscription);
        }

        // --- SORTIES CLÔTURÉES (3 sorties) ---
        for ($i = 1; $i <= 15; $i++) {
            $sortie = new Sortie();

            $nom = "Sortie clôturée #$i - " . $faker->randomElement(['Concert', 'Match', 'Théâtre']);
            $sortie->setNom($cutNom($nom));

            $sortie->setDateHeureDebut($faker->dateTimeBetween('+1 week', '+2 weeks'));
            $sortie->setDuree($faker->numberBetween(120, 240));
            $sortie->setDateLimiteInscription((clone $sortie->getDateHeureDebut())->modify('-1 day'));
            $nbMax = $faker->numberBetween(6, 10);
            $sortie->setNbInscriptionsMax($nbMax);
            $sortie->setInfosSortie("Sortie complète !");

            $lieuNum = $faker->numberBetween(1, 15);
            $sortie->setLieu($this->getReference('lieu_' . $lieuNum, Lieu::class));

            $sortie->setEtat($etatCloturee);

            $participantNum = $faker->numberBetween(10, 19);
            $sortie->setOrganisateur($this->getReference('participant_' . $participantNum, Participant::class));

            $sortie->setSite($sites[$i % 3]);

            $manager->persist($sortie);

            // Remplir complètement
            for ($j = 0; $j < $nbMax; $j++) {
                $inscription = new Inscription();
                $participantNum = (($i * 2 + $j) % 19) + 1;
                $inscription->setParticipant($this->getReference('participant_' . $participantNum, Participant::class));
                $inscription->setSortie($sortie);
                $inscription->setDateInscription($faker->dateTimeBetween('-1 week', '-1 day'));
                $manager->persist($inscription);
            }

            $this->addReference('sortie_cloturee_' . $i, $sortie);
        }

        // --- SORTIE EN COURS ---
        $sortie = new Sortie();
        $sortie->setNom($cutNom("Sortie en cours - Randonnée"));
        $sortie->setDateHeureDebut($faker->dateTimeBetween('-2 hours', 'now'));
        $sortie->setDuree(240);
        $sortie->setDateLimiteInscription((clone $sortie->getDateHeureDebut())->modify('-3 days'));
        $sortie->setNbInscriptionsMax(12);
        $sortie->setInfosSortie("La sortie a déjà commencé !");
        $sortie->setLieu($this->getReference('lieu_11', Lieu::class));
        $sortie->setEtat($etatEnCours);
        $sortie->setOrganisateur($this->getReference('participant_5', Participant::class));
        $sortie->setSite($siteSaintHerblain);

        $manager->persist($sortie);

        for ($j = 1; $j <= 8; $j++) {
            $inscription = new Inscription();
            $inscription->setParticipant($this->getReference('participant_' . $j, Participant::class));
            $inscription->setSortie($sortie);
            $inscription->setDateInscription($faker->dateTimeBetween('-1 week', '-3 days'));
            $manager->persist($inscription);
        }

        // --- SORTIE TERMINÉE ---
        $sortie = new Sortie();
        $sortie->setNom($cutNom("Sortie terminée - Bowling"));
        $sortie->setDateHeureDebut($faker->dateTimeBetween('-1 week', '-2 days'));
        $sortie->setDuree(180);
        $sortie->setDateLimiteInscription((clone $sortie->getDateHeureDebut())->modify('-3 days'));
        $sortie->setNbInscriptionsMax(10);
        $sortie->setInfosSortie("Cette sortie est terminée.");
        $sortie->setLieu($this->getReference('lieu_9', Lieu::class));
        $sortie->setEtat($etatTerminee);
        $sortie->setOrganisateur($this->getReference('participant_6', Participant::class));
        $sortie->setSite($siteChartres);

        $manager->persist($sortie);

        for ($j = 1; $j <= 7; $j++) {
            $inscription = new Inscription();
            $inscription->setParticipant($this->getReference('participant_' . ($j + 5), Participant::class));
            $inscription->setSortie($sortie);
            $inscription->setDateInscription($faker->dateTimeBetween('-2 weeks', '-1 week'));
            $manager->persist($inscription);
        }

        // --- SORTIES ANNULÉES (2 sorties) ---
        for ($i = 1; $i <= 10; $i++) {
            $sortie = new Sortie();
            $sortie->setNom($cutNom("Sortie annulée #$i"));
            $sortie->setDateHeureDebut($faker->dateTimeBetween('+1 week', '+2 weeks'));
            $sortie->setDuree(120);
            $sortie->setDateLimiteInscription((clone $sortie->getDateHeureDebut())->modify('-2 days'));
            $sortie->setNbInscriptionsMax(8);
            $sortie->setInfosSortie("Cette sortie a été annulée.");
            $sortie->setMotifAnnulation($faker->randomElement([
                "Météo défavorable",
                "Trop peu d'inscrits",
                "Problème avec le lieu",
                "Organisateur indisponible"
            ]));

            $lieuNum = $faker->numberBetween(1, 15);
            $sortie->setLieu($this->getReference('lieu_' . $lieuNum, Lieu::class));

            $sortie->setEtat($etatAnnulee);

            $participantNum = $faker->numberBetween(1, 19);
            $sortie->setOrganisateur($this->getReference('participant_' . $participantNum, Participant::class));

            $sortie->setSite($sites[$i % 3]);

            $manager->persist($sortie);

            // Garder les inscriptions (historique)
            for ($j = 0; $j < 4; $j++) {
                $inscription = new Inscription();
                $participantNum = (($i * 4 + $j) % 19) + 1;
                $inscription->setParticipant($this->getReference('participant_' . $participantNum, Participant::class));
                $inscription->setSortie($sortie);
                $inscription->setDateInscription($faker->dateTimeBetween('-1 week', '-2 days'));
                $manager->persist($inscription);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            AppFixtures::class,
            SiteFixtures::class,
            ParticipantFixtures::class,
            LieuFixtures::class,
        ];
    }
}
