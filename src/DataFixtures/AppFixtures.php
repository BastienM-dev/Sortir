<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    // Constantes pour référencer les états dans d'autres fixtures
    public const ETAT_EN_CREATION = 'etat_en_creation';
    public const ETAT_OUVERTE = 'etat_ouverte';
    public const ETAT_CLOTUREE = 'etat_cloturee';
    public const ETAT_EN_COURS = 'etat_en_cours';
    public const ETAT_TERMINEE = 'etat_terminee';
    public const ETAT_ANNULEE = 'etat_annulee';
    public const ETAT_HISTORISEE = 'etat_historisee';

    public function load(ObjectManager $manager): void
    {
        // Création des 7 états selon le diagramme d'états
        $etats = [
            ['libelle' => 'En création', 'reference' => self::ETAT_EN_CREATION],
            ['libelle' => 'Ouverte', 'reference' => self::ETAT_OUVERTE],
            ['libelle' => 'Clôturée', 'reference' => self::ETAT_CLOTUREE],
            ['libelle' => 'En cours', 'reference' => self::ETAT_EN_COURS],
            ['libelle' => 'Terminée', 'reference' => self::ETAT_TERMINEE],
            ['libelle' => 'Annulée', 'reference' => self::ETAT_ANNULEE],
            ['libelle' => 'Historisée', 'reference' => self::ETAT_HISTORISEE],
        ];

        foreach ($etats as $etatData) {
            $etat = new Etat();
            $etat->setLibelle($etatData['libelle']);
            $manager->persist($etat);
            
            // Ajouter une référence pour utiliser dans d'autres fixtures
            $this->addReference($etatData['reference'], $etat);
        }

        $manager->flush();
    }
}
