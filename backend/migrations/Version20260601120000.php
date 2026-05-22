<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260601120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout TypeConditionnement et nouveaux champs Article (gestionDlc, gestionNumeroSerie, typeConditionnement, imagePath)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE type_conditionnement (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(100) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE article ADD gestion_dlc TINYINT(1) NOT NULL DEFAULT 0, ADD gestion_numero_serie TINYINT(1) NOT NULL DEFAULT 0, ADD type_conditionnement_id INT DEFAULT NULL, ADD image_path VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E66D6C08B61 FOREIGN KEY (type_conditionnement_id) REFERENCES type_conditionnement (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_23A0E66D6C08B61 ON article (type_conditionnement_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E66D6C08B61');
        $this->addSql('DROP INDEX IDX_23A0E66D6C08B61 ON article');
        $this->addSql('ALTER TABLE article DROP gestion_dlc, DROP gestion_numero_serie, DROP type_conditionnement_id, DROP image_path');
        $this->addSql('DROP TABLE type_conditionnement');
    }
}
