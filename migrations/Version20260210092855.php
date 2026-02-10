<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260210092855 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE etats (no_etat INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(30) NOT NULL, PRIMARY KEY (no_etat)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE inscriptions (date_inscription DATETIME NOT NULL, participants_no_participant INT NOT NULL, sorties_no_sortie INT NOT NULL, INDEX IDX_74E0281CEF759E07 (participants_no_participant), INDEX IDX_74E0281CC731F823 (sorties_no_sortie), PRIMARY KEY (participants_no_participant, sorties_no_sortie)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE lieux (no_lieu INT AUTO_INCREMENT NOT NULL, nom_lieu VARCHAR(30) NOT NULL, rue VARCHAR(30) DEFAULT NULL, latitude NUMERIC(10, 8) DEFAULT NULL, longitude NUMERIC(11, 8) DEFAULT NULL, villes_no_ville INT NOT NULL, INDEX IDX_9E44A8AE395FAFC3 (villes_no_ville), PRIMARY KEY (no_lieu)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE participants (no_participant INT AUTO_INCREMENT NOT NULL, pseudo VARCHAR(30) NOT NULL, nom VARCHAR(30) NOT NULL, prenom VARCHAR(30) NOT NULL, telephone VARCHAR(20) DEFAULT NULL, mail VARCHAR(180) NOT NULL, mot_de_passe VARCHAR(255) NOT NULL, administrateur TINYINT DEFAULT 0 NOT NULL, actif TINYINT DEFAULT 1 NOT NULL, photo_filename VARCHAR(255) DEFAULT NULL, sites_no_site INT NOT NULL, UNIQUE INDEX UNIQ_716970925126AC48 (mail), INDEX IDX_7169709251C3F4BB (sites_no_site), UNIQUE INDEX participants_pseudo_uk (pseudo), PRIMARY KEY (no_participant)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE sites (no_site INT AUTO_INCREMENT NOT NULL, nom_site VARCHAR(30) NOT NULL, PRIMARY KEY (no_site)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE sorties (no_sortie INT AUTO_INCREMENT NOT NULL, nom VARCHAR(30) NOT NULL, datedebut DATETIME NOT NULL, duree INT DEFAULT NULL, datecloture DATETIME NOT NULL, nbinscriptionsmax INT NOT NULL, descriptioninfos LONGTEXT DEFAULT NULL, urlPhoto VARCHAR(250) DEFAULT NULL, photo_filename VARCHAR(255) DEFAULT NULL, motif_annulation LONGTEXT DEFAULT NULL, lieux_no_lieu INT NOT NULL, etats_no_etat INT NOT NULL, organisateur INT NOT NULL, sites_no_site INT NOT NULL, INDEX IDX_488163E84E23F7D7 (lieux_no_lieu), INDEX IDX_488163E8FCD21D77 (etats_no_etat), INDEX IDX_488163E84BD76D44 (organisateur), INDEX IDX_488163E851C3F4BB (sites_no_site), PRIMARY KEY (no_sortie)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE villes (no_ville INT AUTO_INCREMENT NOT NULL, nom_ville VARCHAR(30) NOT NULL, code_postal VARCHAR(10) NOT NULL, PRIMARY KEY (no_ville)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE inscriptions ADD CONSTRAINT FK_74E0281CEF759E07 FOREIGN KEY (participants_no_participant) REFERENCES participants (no_participant)');
        $this->addSql('ALTER TABLE inscriptions ADD CONSTRAINT FK_74E0281CC731F823 FOREIGN KEY (sorties_no_sortie) REFERENCES sorties (no_sortie)');
        $this->addSql('ALTER TABLE lieux ADD CONSTRAINT FK_9E44A8AE395FAFC3 FOREIGN KEY (villes_no_ville) REFERENCES villes (no_ville)');
        $this->addSql('ALTER TABLE participants ADD CONSTRAINT FK_7169709251C3F4BB FOREIGN KEY (sites_no_site) REFERENCES sites (no_site)');
        $this->addSql('ALTER TABLE sorties ADD CONSTRAINT FK_488163E84E23F7D7 FOREIGN KEY (lieux_no_lieu) REFERENCES lieux (no_lieu)');
        $this->addSql('ALTER TABLE sorties ADD CONSTRAINT FK_488163E8FCD21D77 FOREIGN KEY (etats_no_etat) REFERENCES etats (no_etat)');
        $this->addSql('ALTER TABLE sorties ADD CONSTRAINT FK_488163E84BD76D44 FOREIGN KEY (organisateur) REFERENCES participants (no_participant)');
        $this->addSql('ALTER TABLE sorties ADD CONSTRAINT FK_488163E851C3F4BB FOREIGN KEY (sites_no_site) REFERENCES sites (no_site)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inscriptions DROP FOREIGN KEY FK_74E0281CEF759E07');
        $this->addSql('ALTER TABLE inscriptions DROP FOREIGN KEY FK_74E0281CC731F823');
        $this->addSql('ALTER TABLE lieux DROP FOREIGN KEY FK_9E44A8AE395FAFC3');
        $this->addSql('ALTER TABLE participants DROP FOREIGN KEY FK_7169709251C3F4BB');
        $this->addSql('ALTER TABLE sorties DROP FOREIGN KEY FK_488163E84E23F7D7');
        $this->addSql('ALTER TABLE sorties DROP FOREIGN KEY FK_488163E8FCD21D77');
        $this->addSql('ALTER TABLE sorties DROP FOREIGN KEY FK_488163E84BD76D44');
        $this->addSql('ALTER TABLE sorties DROP FOREIGN KEY FK_488163E851C3F4BB');
        $this->addSql('DROP TABLE etats');
        $this->addSql('DROP TABLE inscriptions');
        $this->addSql('DROP TABLE lieux');
        $this->addSql('DROP TABLE participants');
        $this->addSql('DROP TABLE sites');
        $this->addSql('DROP TABLE sorties');
        $this->addSql('DROP TABLE villes');
    }
}
