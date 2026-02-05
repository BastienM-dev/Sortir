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

        // Créer 15 lieux variés avec Faker
        $typesLieux = [
            'Restaurant', 'Bar', 'Parc', 'Cinéma', 'Musée',
            'Salle de sport', 'Théâtre', 'Bowling', 'Escape Game',
            'Karting', 'Plage', 'Forêt', 'Stade', 'Piscine', 'Lac'
        ];

        for ($i = 1; $i <= 15; $i++) {
            $lieu = new Lieu();

            // Nom du lieu : Type + nom de ville
            $type = $typesLieux[$i - 1];
            $lieu->setNom($type . ' ' . $faker->company());

            // Adresse
            $lieu->setRue($faker->streetAddress());

            // Coordonnées GPS aléatoires (France métropolitaine)
            // Latitude : entre 42 (sud) et 51 (nord)
            // Longitude : entre -5 (ouest) et 8 (est)
            $lieu->setLatitude($faker->randomFloat(6, 42, 51));
            $lieu->setLongitude($faker->randomFloat(6, -5, 8));

            // Associer à une ville aléatoire
            $villeIndex = $faker->numberBetween(1, 10);
            $lieu->setVille($this->getReference('ville_' . $villeIndex, Ville::class));

            $manager->persist($lieu);

            // Ajouter une référence pour utiliser dans SortieFixtures
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