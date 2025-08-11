<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250702140508 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE adresse (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, code_postal INTEGER NOT NULL, rue CLOB NOT NULL, ville CLOB NOT NULL)');
        $this->addSql('CREATE TABLE alerte (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, utilisateur_id INTEGER DEFAULT NULL, description CLOB NOT NULL, titre VARCHAR(255) NOT NULL, CONSTRAINT FK_3AE753AFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_3AE753AFB88E14F ON alerte (utilisateur_id)');
        $this->addSql('CREATE TABLE appartient (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, utilisateur_id INTEGER DEFAULT NULL, produit_id INTEGER DEFAULT NULL, date_ajout DATETIME NOT NULL, date_modification DATETIME NOT NULL, CONSTRAINT FK_4201BAA7FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_4201BAA7F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_4201BAA7FB88E14F ON appartient (utilisateur_id)');
        $this->addSql('CREATE INDEX IDX_4201BAA7F347EFB ON appartient (produit_id)');
        $this->addSql('CREATE TABLE avis (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, description CLOB NOT NULL, note VARCHAR(255) NOT NULL, de VARCHAR(255) NOT NULL, pour VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE TABLE categorie (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE TABLE etat (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE TABLE favoris (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, utilisateur_id INTEGER DEFAULT NULL, produit_id INTEGER DEFAULT NULL, CONSTRAINT FK_8933C432FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_8933C432F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_8933C432FB88E14F ON favoris (utilisateur_id)');
        $this->addSql('CREATE INDEX IDX_8933C432F347EFB ON favoris (produit_id)');
        $this->addSql('CREATE TABLE paiement (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, produit_id INTEGER DEFAULT NULL, utilisateur_id INTEGER DEFAULT NULL, type VARCHAR(255) NOT NULL, CONSTRAINT FK_B1DC7A1EF347EFB FOREIGN KEY (produit_id) REFERENCES produit (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_B1DC7A1EFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B1DC7A1EF347EFB ON paiement (produit_id)');
        $this->addSql('CREATE INDEX IDX_B1DC7A1EFB88E14F ON paiement (utilisateur_id)');
        $this->addSql('CREATE TABLE possede (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, utilisateur_id INTEGER DEFAULT NULL, role_id INTEGER DEFAULT NULL, CONSTRAINT FK_3D0B1508FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_3D0B1508D60322AC FOREIGN KEY (role_id) REFERENCES role (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_3D0B1508FB88E14F ON possede (utilisateur_id)');
        $this->addSql('CREATE INDEX IDX_3D0B1508D60322AC ON possede (role_id)');
        $this->addSql('CREATE TABLE produit (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, etat_id INTEGER DEFAULT NULL, nom VARCHAR(255) NOT NULL, prix_initial DOUBLE PRECISION NOT NULL, libelle VARCHAR(255) NOT NULL, type_vente VARCHAR(255) NOT NULL, description CLOB NOT NULL, CONSTRAINT FK_29A5EC27D5E86FF FOREIGN KEY (etat_id) REFERENCES etat (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_29A5EC27D5E86FF ON produit (etat_id)');
        $this->addSql('CREATE TABLE produit_categorie (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, produit_id INTEGER DEFAULT NULL, categorie_id INTEGER DEFAULT NULL, CONSTRAINT FK_CDEA88D8F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_CDEA88D8BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_CDEA88D8F347EFB ON produit_categorie (produit_id)');
        $this->addSql('CREATE INDEX IDX_CDEA88D8BCF5E72D ON produit_categorie (categorie_id)');
        $this->addSql('CREATE TABLE role (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, description CLOB NOT NULL)');
        $this->addSql('CREATE TABLE signale (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, produit_id INTEGER DEFAULT NULL, signalement_id INTEGER DEFAULT NULL, CONSTRAINT FK_2279705CF347EFB FOREIGN KEY (produit_id) REFERENCES produit (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_2279705C65C5E57E FOREIGN KEY (signalement_id) REFERENCES signalement (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_2279705CF347EFB ON signale (produit_id)');
        $this->addSql('CREATE INDEX IDX_2279705C65C5E57E ON signale (signalement_id)');
        $this->addSql('CREATE TABLE signalement (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nature_signalement CLOB NOT NULL)');
        $this->addSql('CREATE TABLE utilisateur (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, avis_id INTEGER DEFAULT NULL, adresse_id INTEGER DEFAULT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, CONSTRAINT FK_1D1C63B3197E709F FOREIGN KEY (avis_id) REFERENCES avis (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_1D1C63B34DE7DC5C FOREIGN KEY (adresse_id) REFERENCES adresse (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_1D1C63B3197E709F ON utilisateur (avis_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1D1C63B34DE7DC5C ON utilisateur (adresse_id)');
        $this->addSql('DROP TABLE article');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE article (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(255) NOT NULL COLLATE "BINARY", content CLOB NOT NULL COLLATE "BINARY", image VARCHAR(255) NOT NULL COLLATE "BINARY", created_at DATETIME NOT NULL)');
        $this->addSql('DROP TABLE adresse');
        $this->addSql('DROP TABLE alerte');
        $this->addSql('DROP TABLE appartient');
        $this->addSql('DROP TABLE avis');
        $this->addSql('DROP TABLE categorie');
        $this->addSql('DROP TABLE etat');
        $this->addSql('DROP TABLE favoris');
        $this->addSql('DROP TABLE paiement');
        $this->addSql('DROP TABLE possede');
        $this->addSql('DROP TABLE produit');
        $this->addSql('DROP TABLE produit_categorie');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE signale');
        $this->addSql('DROP TABLE signalement');
        $this->addSql('DROP TABLE utilisateur');
    }
}
