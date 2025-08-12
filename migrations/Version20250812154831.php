<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250812154831 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE conversation (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, acheteur_id INTEGER NOT NULL, vendeur_id INTEGER NOT NULL, produit_id INTEGER NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, is_active BOOLEAN DEFAULT 1 NOT NULL, CONSTRAINT FK_8A8E26E996A7BB5F FOREIGN KEY (acheteur_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_8A8E26E9858C065E FOREIGN KEY (vendeur_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_8A8E26E9F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_8A8E26E996A7BB5F ON conversation (acheteur_id)');
        $this->addSql('CREATE INDEX IDX_8A8E26E9858C065E ON conversation (vendeur_id)');
        $this->addSql('CREATE INDEX idx_conversation_participants ON conversation (acheteur_id, vendeur_id)');
        $this->addSql('CREATE INDEX idx_conversation_produit ON conversation (produit_id)');
        $this->addSql('CREATE TABLE image (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, produit_id INTEGER NOT NULL, nom_fichier VARCHAR(255) NOT NULL, chemin_fichier VARCHAR(255) NOT NULL, alt_text VARCHAR(100) DEFAULT NULL, ordre INTEGER NOT NULL, date_upload DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_C53D045FF347EFB FOREIGN KEY (produit_id) REFERENCES produit (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_C53D045FF347EFB ON image (produit_id)');
        $this->addSql('CREATE TABLE message (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, conversation_id INTEGER NOT NULL, auteur_id INTEGER NOT NULL, contenu CLOB NOT NULL, lu BOOLEAN DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, lu_at DATETIME DEFAULT NULL, is_edited BOOLEAN DEFAULT 0 NOT NULL, edited_at DATETIME DEFAULT NULL, type VARCHAR(50) DEFAULT \'text\' NOT NULL, CONSTRAINT FK_B6BD307F9AC0396 FOREIGN KEY (conversation_id) REFERENCES conversation (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_B6BD307F60BB6FE6 FOREIGN KEY (auteur_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX idx_message_conversation ON message (conversation_id)');
        $this->addSql('CREATE INDEX idx_message_auteur ON message (auteur_id)');
        $this->addSql('CREATE INDEX idx_message_lu ON message (lu)');
        $this->addSql('CREATE INDEX idx_message_created_at ON message (created_at)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE conversation');
        $this->addSql('DROP TABLE image');
        $this->addSql('DROP TABLE message');
    }
}
