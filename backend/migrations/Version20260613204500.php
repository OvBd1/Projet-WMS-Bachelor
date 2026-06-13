<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260613204500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE commande CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE emplacement CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE ligne_commande CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE ligne_reception CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE reception CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE stock CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE tiers CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE transfert_emplacement CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE type_conditionnement CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE type_emplacement CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE utilisateur ADD nom VARCHAR(100) DEFAULT NULL, ADD prenom VARCHAR(100) DEFAULT NULL, CHANGE created_at created_at DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE commande CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE emplacement CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE ligne_commande CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE ligne_reception CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE reception CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE stock CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE tiers CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE transfert_emplacement CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE type_conditionnement CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE type_emplacement CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE utilisateur DROP nom, DROP prenom, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
    }
}
