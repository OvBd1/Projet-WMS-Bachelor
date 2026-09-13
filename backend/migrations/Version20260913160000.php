<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Aligne le nom de l'index unique du numéro de commande sur celui généré par Doctrine.
 *
 * Version20260701120000 l'avait nommé à la main (UNIQ_commande_numero) : le schéma issu
 * des migrations différait alors du mapping et doctrine:schema:validate échouait.
 */
final class Version20260913160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Renommage de l\'index unique commande.numero_commande (nom généré par Doctrine)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE commande RENAME INDEX UNIQ_commande_numero TO UNIQ_6EEAA67DCFFD611D');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE commande RENAME INDEX UNIQ_6EEAA67DCFFD611D TO UNIQ_commande_numero');
    }
}
