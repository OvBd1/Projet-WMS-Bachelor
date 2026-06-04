<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260604102812 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY `FK_23A0E66D6C08B61`');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E666D435DE7 FOREIGN KEY (type_conditionnement_id) REFERENCES type_conditionnement (id)');
        $this->addSql('ALTER TABLE article RENAME INDEX idx_23a0e66d6c08b61 TO IDX_23A0E666D435DE7');
        $this->addSql('ALTER TABLE commande ADD tiers_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67D68B77723 FOREIGN KEY (tiers_id) REFERENCES tiers (id)');
        $this->addSql('CREATE INDEX IDX_6EEAA67D68B77723 ON commande (tiers_id)');
        $this->addSql('ALTER TABLE reception DROP FOREIGN KEY `FK_reception_tiers`');
        $this->addSql('ALTER TABLE reception CHANGE statut statut VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE reception ADD CONSTRAINT FK_50D6852F68B77723 FOREIGN KEY (tiers_id) REFERENCES tiers (id)');
        $this->addSql('ALTER TABLE reception RENAME INDEX idx_reception_tiers TO IDX_50D6852F68B77723');
        $this->addSql('ALTER TABLE tiers ADD code VARCHAR(50) NOT NULL, ADD code_postal VARCHAR(20) DEFAULT NULL, ADD ville VARCHAR(255) DEFAULT NULL, ADD pays VARCHAR(100) DEFAULT NULL, DROP telephone, DROP adresse, CHANGE type type VARCHAR(20) NOT NULL, CHANGE email rue VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_16473BA277153098 ON tiers (code)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E666D435DE7');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT `FK_23A0E66D6C08B61` FOREIGN KEY (type_conditionnement_id) REFERENCES type_conditionnement (id) ON UPDATE NO ACTION ON DELETE SET NULL');
        $this->addSql('ALTER TABLE article RENAME INDEX idx_23a0e666d435de7 TO IDX_23A0E66D6C08B61');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67D68B77723');
        $this->addSql('DROP INDEX IDX_6EEAA67D68B77723 ON commande');
        $this->addSql('ALTER TABLE commande DROP tiers_id');
        $this->addSql('ALTER TABLE reception DROP FOREIGN KEY FK_50D6852F68B77723');
        $this->addSql('ALTER TABLE reception CHANGE statut statut VARCHAR(20) DEFAULT \'EN_ATTENTE\' NOT NULL');
        $this->addSql('ALTER TABLE reception ADD CONSTRAINT `FK_reception_tiers` FOREIGN KEY (tiers_id) REFERENCES tiers (id) ON UPDATE NO ACTION ON DELETE SET NULL');
        $this->addSql('ALTER TABLE reception RENAME INDEX idx_50d6852f68b77723 TO IDX_reception_tiers');
        $this->addSql('DROP INDEX UNIQ_16473BA277153098 ON tiers');
        $this->addSql('ALTER TABLE tiers ADD email VARCHAR(255) DEFAULT NULL, ADD telephone VARCHAR(30) DEFAULT NULL, ADD adresse LONGTEXT DEFAULT NULL, DROP code, DROP rue, DROP code_postal, DROP ville, DROP pays, CHANGE type type VARCHAR(20) DEFAULT \'FOURNISSEUR\' NOT NULL');
    }
}
