<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260601150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout table tiers, statut + tiers_id sur reception, dlc + numero_serie sur ligne_reception';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE tiers (
            id INT AUTO_INCREMENT NOT NULL,
            nom VARCHAR(255) NOT NULL,
            type VARCHAR(20) NOT NULL DEFAULT 'FOURNISSEUR',
            email VARCHAR(255) DEFAULT NULL,
            telephone VARCHAR(30) DEFAULT NULL,
            adresse LONGTEXT DEFAULT NULL,
            PRIMARY KEY (id)
        ) DEFAULT CHARACTER SET utf8mb4");

        $this->addSql("ALTER TABLE reception
            ADD statut VARCHAR(20) NOT NULL DEFAULT 'EN_ATTENTE',
            ADD tiers_id INT DEFAULT NULL");

        $this->addSql('ALTER TABLE reception ADD CONSTRAINT FK_reception_tiers FOREIGN KEY (tiers_id) REFERENCES tiers (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_reception_tiers ON reception (tiers_id)');

        $this->addSql('ALTER TABLE ligne_reception
            ADD dlc DATE DEFAULT NULL,
            ADD numero_serie VARCHAR(100) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ligne_reception DROP dlc, DROP numero_serie');
        $this->addSql('ALTER TABLE reception DROP FOREIGN KEY FK_reception_tiers');
        $this->addSql('DROP INDEX IDX_reception_tiers ON reception');
        $this->addSql('ALTER TABLE reception DROP statut, DROP tiers_id');
        $this->addSql('DROP TABLE tiers');
    }
}
