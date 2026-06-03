<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260603072023 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tiers (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, type VARCHAR(20) NOT NULL, email VARCHAR(255) DEFAULT NULL, telephone VARCHAR(30) DEFAULT NULL, adresse LONGTEXT DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY `FK_23A0E66D6C08B61`');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E666D435DE7 FOREIGN KEY (type_conditionnement_id) REFERENCES type_conditionnement (id)');
        $this->addSql('ALTER TABLE article RENAME INDEX idx_23a0e66d6c08b61 TO IDX_23A0E666D435DE7');
        $this->addSql('ALTER TABLE ligne_reception ADD dlc DATE DEFAULT NULL, ADD numero_serie VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE reception ADD statut VARCHAR(20) NOT NULL, ADD tiers_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE reception ADD CONSTRAINT FK_50D6852F68B77723 FOREIGN KEY (tiers_id) REFERENCES tiers (id)');
        $this->addSql('CREATE INDEX IDX_50D6852F68B77723 ON reception (tiers_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE tiers');
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E666D435DE7');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT `FK_23A0E66D6C08B61` FOREIGN KEY (type_conditionnement_id) REFERENCES type_conditionnement (id) ON UPDATE NO ACTION ON DELETE SET NULL');
        $this->addSql('ALTER TABLE article RENAME INDEX idx_23a0e666d435de7 TO IDX_23A0E66D6C08B61');
        $this->addSql('ALTER TABLE ligne_reception DROP dlc, DROP numero_serie');
        $this->addSql('ALTER TABLE reception DROP FOREIGN KEY FK_50D6852F68B77723');
        $this->addSql('DROP INDEX IDX_50D6852F68B77723 ON reception');
        $this->addSql('ALTER TABLE reception DROP statut, DROP tiers_id');
    }
}
