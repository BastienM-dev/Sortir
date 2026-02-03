<?php

namespace App\DataFixtures;

use App\Entity\Site;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SiteFixtures extends Fixture
{
    // Constantes pour référencer les sites dans d'autres fixtures
    public const SITE_SAINT_HERBLAIN = 'site_saint_herblain';
    public const SITE_CHARTRES = 'site_chartres';
    public const SITE_LA_ROCHE = 'site_la_roche';

    public function load(ObjectManager $manager): void
    {
        // Création des 3 sites ENI
        $sites = [
            ['nom' => 'SAINT-HERBLAIN', 'reference' => self::SITE_SAINT_HERBLAIN],
            ['nom' => 'CHARTRES-DE-BRETAGNE', 'reference' => self::SITE_CHARTRES],
            ['nom' => 'LA ROCHE-SUR-YON', 'reference' => self::SITE_LA_ROCHE],
        ];

        foreach ($sites as $siteData) {
            $site = new Site();
            $site->setNom($siteData['nom']);
            $manager->persist($site);
            
            // Ajouter une référence pour utiliser dans d'autres fixtures
            $this->addReference($siteData['reference'], $site);
        }

        $manager->flush();
    }
}
