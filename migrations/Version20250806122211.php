<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250806122211 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE image (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, produit_id INTEGER NOT NULL, nom_fichier VARCHAR(255) NOT NULL, chemin_fichier VARCHAR(255) NOT NULL, alt_text VARCHAR(100) DEFAULT NULL, ordre INTEGER NOT NULL, date_upload DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_C53D045FF347EFB FOREIGN KEY (produit_id) REFERENCES produit (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_C53D045FF347EFB ON image (produit_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__produit AS SELECT id, etat_id, nom, prix_initial, libelle, description, images FROM produit');
        $this->addSql('DROP TABLE produit');
        $this->addSql('CREATE TABLE produit (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, etat_id INTEGER DEFAULT NULL, nom VARCHAR(255) NOT NULL, prix_initial DOUBLE PRECISION NOT NULL, libelle VARCHAR(255) NOT NULL, description CLOB NOT NULL, images CLOB DEFAULT NULL --(DC2Type:json)
        , CONSTRAINT FK_29A5EC27D5E86FF FOREIGN KEY (etat_id) REFERENCES etat (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO produit (id, etat_id, nom, prix_initial, libelle, description, images) SELECT id, etat_id, nom, prix_initial, libelle, description, images FROM __temp__produit');
        $this->addSql('DROP TABLE __temp__produit');
        $this->addSql('CREATE INDEX IDX_29A5EC27D5E86FF ON produit (etat_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE image');
        $this->addSql('CREATE TEMPORARY TABLE __temp__produit AS SELECT id, etat_id, nom, prix_initial, libelle, description, images FROM produit');
        $this->addSql('DROP TABLE produit');
        $this->addSql('CREATE TABLE produit (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, etat_id INTEGER DEFAULT NULL, nom VARCHAR(255) NOT NULL, prix_initial DOUBLE PRECISION NOT NULL, libelle VARCHAR(255) NOT NULL, description CLOB NOT NULL, images CLOB DEFAULT NULL, type_vente VARCHAR(255) NOT NULL, CONSTRAINT FK_29A5EC27D5E86FF FOREIGN KEY (etat_id) REFERENCES etat (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO produit (id, etat_id, nom, prix_initial, libelle, description, images) SELECT id, etat_id, nom, prix_initial, libelle, description, images FROM __temp__produit');
        $this->addSql('DROP TABLE __temp__produit');
        $this->addSql('CREATE INDEX IDX_29A5EC27D5E86FF ON produit (etat_id)');
    }
}
