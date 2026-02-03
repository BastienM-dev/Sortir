<?php

namespace App\DataFixtures;

use App\Entity\Participant;
use App\Entity\Site;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ParticipantFixtures extends Fixture implements DependentFixtureInterface
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Créer un compte administrateur pour les tests
        $admin = new Participant();
        $admin->setPseudo('admin');
        $admin->setNom('Administrateur');
        $admin->setPrenom('Admin');
        $admin->setMail('admin@sortir.com');
        $admin->setTelephone($faker->phoneNumber());
        $admin->setAdministrateur(true);
        $admin->setActif(true);
        $admin->setSite($this->getReference(SiteFixtures::SITE_SAINT_HERBLAIN, Site::class));
        
        // Hash du mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($admin, 'admin123');
        $admin->setPassword($hashedPassword);
        
        $manager->persist($admin);

        // Créer 19 participants normaux avec Faker
        for ($i = 1; $i <= 19; $i++) {
            $participant = new Participant();
            
            $firstName = $faker->firstName();
            $lastName = $faker->lastName();
            
            $participant->setPseudo(strtolower($firstName) . $i);
            $participant->setNom($lastName);
            $participant->setPrenom($firstName);
            $participant->setMail(strtolower($firstName) . '.' . strtolower($lastName) . '@example.com');
            $participant->setTelephone($faker->phoneNumber());
            $participant->setAdministrateur(false);
            $participant->setActif($faker->boolean(90)); // 90% actifs
            
            // Répartir les participants sur les 3 sites
            $siteIndex = $i % 3;
            if ($siteIndex === 0) {
                $participant->setSite($this->getReference(SiteFixtures::SITE_SAINT_HERBLAIN, Site::class));
            } elseif ($siteIndex === 1) {
                $participant->setSite($this->getReference(SiteFixtures::SITE_CHARTRES, Site::class));
            } else {
                $participant->setSite($this->getReference(SiteFixtures::SITE_LA_ROCHE, Site::class));
            }
            
            // Mot de passe identique pour tous : "password"
            $hashedPassword = $this->passwordHasher->hashPassword($participant, 'password');
            $participant->setPassword($hashedPassword);
            
            $manager->persist($participant);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            SiteFixtures::class,
        ];
    }
}
