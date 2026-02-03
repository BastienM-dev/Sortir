<?php

namespace App\DataFixtures;

use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class VilleFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Créer 10 villes avec Faker
        for ($i = 1; $i <= 10; $i++) {
            $ville = new Ville();
            $ville->setNom($faker->city());
            $ville->setCodePostal($faker->postcode());
            
            $manager->persist($ville);
            
            // Ajouter une référence pour utiliser dans d'autres fixtures
            $this->addReference('ville_' . $i, $ville);
        }

        $manager->flush();
    }
}
