<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250812160923 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__signale AS SELECT id, produit_id, signalement_id FROM signale');
        $this->addSql('DROP TABLE signale');
        $this->addSql('CREATE TABLE signale (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, produit_id INTEGER NOT NULL, signalement_id INTEGER NOT NULL, signale_par_id INTEGER NOT NULL, traite_par_id INTEGER DEFAULT NULL, commentaire CLOB DEFAULT NULL, date_signalement DATETIME NOT NULL, statut VARCHAR(20) DEFAULT \'en_attente\' NOT NULL, date_traitement DATETIME DEFAULT NULL, reponse_admin CLOB DEFAULT NULL, CONSTRAINT FK_2279705CF347EFB FOREIGN KEY (produit_id) REFERENCES produit (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_2279705C65C5E57E FOREIGN KEY (signalement_id) REFERENCES signalement (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_2279705CAE190A20 FOREIGN KEY (signale_par_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_2279705C167FABE8 FOREIGN KEY (traite_par_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO signale (id, produit_id, signalement_id) SELECT id, produit_id, signalement_id FROM __temp__signale');
        $this->addSql('DROP TABLE __temp__signale');
        $this->addSql('CREATE INDEX IDX_2279705C65C5E57E ON signale (signalement_id)');
        $this->addSql('CREATE INDEX IDX_2279705CF347EFB ON signale (produit_id)');
        $this->addSql('CREATE INDEX IDX_2279705CAE190A20 ON signale (signale_par_id)');
        $this->addSql('CREATE INDEX IDX_2279705C167FABE8 ON signale (traite_par_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__signale AS SELECT id, produit_id, signalement_id FROM signale');
        $this->addSql('DROP TABLE signale');
        $this->addSql('CREATE TABLE signale (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, produit_id INTEGER DEFAULT NULL, signalement_id INTEGER DEFAULT NULL, CONSTRAINT FK_2279705CF347EFB FOREIGN KEY (produit_id) REFERENCES produit (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_2279705C65C5E57E FOREIGN KEY (signalement_id) REFERENCES signalement (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO signale (id, produit_id, signalement_id) SELECT id, produit_id, signalement_id FROM __temp__signale');
        $this->addSql('DROP TABLE __temp__signale');
        $this->addSql('CREATE INDEX IDX_2279705CF347EFB ON signale (produit_id)');
        $this->addSql('CREATE INDEX IDX_2279705C65C5E57E ON signale (signalement_id)');
    }
}
