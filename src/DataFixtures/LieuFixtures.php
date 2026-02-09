<?php

namespace App\DataFixtures;

use App\Entity\Lieu;
use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class LieuFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // ⚠️ Sécurité : évite l'erreur "Data too long for column 'nom_lieu'"
        // Mets 30 si ta colonne lieux.nom_lieu est VARCHAR(30). Sinon adapte.
        $maxNomLieu = 30;
        $cutNom = static fn (string $s): string => mb_substr($s, 0, $maxNomLieu);

        // Créer 15 lieux variés
        $typesLieux = [
            'Restaurant', 'Bar', 'Parc', 'Cinéma', 'Musée',
            'Salle de sport', 'Théâtre', 'Bowling', 'Escape Game',
            'Karting', 'Plage', 'Forêt', 'Stade', 'Piscine', 'Lac'
        ];

        for ($i = 1; $i <= 15; $i++) {
            $lieu = new Lieu();

            // Nom du lieu : Type + nom (tronqué)
            $type = $typesLieux[$i - 1];
            $nomLieu = $type . ' ' . $faker->company();
            $lieu->setNom($cutNom($nomLieu));

            // Adresse
            $lieu->setRue($faker->streetAddress());

            // Coordonnées GPS (France métropolitaine)
            $lieu->setLatitude($faker->randomFloat(6, 42, 51));
            $lieu->setLongitude($faker->randomFloat(6, -5, 8));

            // Associer à une ville aléatoire (références créées dans VilleFixtures)
            $villeIndex = $faker->numberBetween(1, 10);
            $lieu->setVille($this->getReference('ville_' . $villeIndex, Ville::class));

            $manager->persist($lieu);

            // Référence pour SortieFixtures
            $this->addReference('lieu_' . $i, $lieu);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            VilleFixtures::class,
        ];
    }
}
