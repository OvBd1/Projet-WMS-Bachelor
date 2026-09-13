<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260701120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout numero_commande et date_expedition sur commande';
    }

    public function up(Schema $schema): void
    {
        // commande.tiers_id est déjà posée par Version20260604102812 : ne pas la recréer.
        $this->addSql('ALTER TABLE commande
            ADD numero_commande VARCHAR(30) DEFAULT NULL,
            ADD date_expedition DATE DEFAULT NULL');

        // Backfill des commandes existantes avec un numéro lisible basé sur l'id.
        $this->addSql("UPDATE commande SET numero_commande = CONCAT('CMD-', LPAD(id, 5, '0')) WHERE numero_commande IS NULL");

        $this->addSql('ALTER TABLE commande CHANGE numero_commande numero_commande VARCHAR(30) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_commande_numero ON commande (numero_commande)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_commande_numero ON commande');
        $this->addSql('ALTER TABLE commande DROP numero_commande, DROP date_expedition');
    }
}
