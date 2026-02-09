<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260209125825 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inscriptions ADD CONSTRAINT FK_74E0281CEF759E07 FOREIGN KEY (participants_no_participant) REFERENCES participants (no_participant)');
        $this->addSql('ALTER TABLE inscriptions ADD CONSTRAINT FK_74E0281CC731F823 FOREIGN KEY (sorties_no_sortie) REFERENCES sorties (no_sortie)');
        $this->addSql('ALTER TABLE lieux ADD CONSTRAINT FK_9E44A8AE395FAFC3 FOREIGN KEY (villes_no_ville) REFERENCES villes (no_ville)');
        $this->addSql('ALTER TABLE participants ADD CONSTRAINT FK_7169709251C3F4BB FOREIGN KEY (sites_no_site) REFERENCES sites (no_site)');
        $this->addSql('ALTER TABLE sorties ADD photo_filename VARCHAR(255) DEFAULT NULL');
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
        $this->addSql('ALTER TABLE sorties DROP photo_filename');
    }
}
