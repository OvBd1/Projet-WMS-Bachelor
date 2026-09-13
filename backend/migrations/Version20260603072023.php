<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration neutralisée.
 *
 * Générée par doctrine:migrations:diff contre une base locale désynchronisée, elle
 * recréait la table `tiers` et les colonnes déjà posées par Version20260601150000,
 * ce qui provoquait SQLSTATE[42S01] sur une base vierge. Les ajustements de schéma
 * restants sont couverts par Version20260604102812.
 *
 * Conservée plutôt que supprimée : elle peut figurer comme exécutée sur des bases
 * existantes.
 */
final class Version20260603072023 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Migration neutralisée (doublon de Version20260601150000)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('SELECT 1');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('SELECT 1');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
