<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260604105110 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article ADD created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, ADD updated_at DATETIME DEFAULT NULL, ADD created_by_id INT DEFAULT NULL, ADD updated_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E66B03A8386 FOREIGN KEY (created_by_id) REFERENCES utilisateur (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E66896DBBDE FOREIGN KEY (updated_by_id) REFERENCES utilisateur (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_23A0E66B03A8386 ON article (created_by_id)');
        $this->addSql('CREATE INDEX IDX_23A0E66896DBBDE ON article (updated_by_id)');
        $this->addSql('ALTER TABLE commande ADD created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, ADD updated_at DATETIME DEFAULT NULL, ADD created_by_id INT DEFAULT NULL, ADD updated_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DB03A8386 FOREIGN KEY (created_by_id) REFERENCES utilisateur (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67D896DBBDE FOREIGN KEY (updated_by_id) REFERENCES utilisateur (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_6EEAA67DB03A8386 ON commande (created_by_id)');
        $this->addSql('CREATE INDEX IDX_6EEAA67D896DBBDE ON commande (updated_by_id)');
        $this->addSql('ALTER TABLE emplacement ADD created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, ADD updated_at DATETIME DEFAULT NULL, ADD created_by_id INT DEFAULT NULL, ADD updated_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE emplacement ADD CONSTRAINT FK_C0CF65F6B03A8386 FOREIGN KEY (created_by_id) REFERENCES utilisateur (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE emplacement ADD CONSTRAINT FK_C0CF65F6896DBBDE FOREIGN KEY (updated_by_id) REFERENCES utilisateur (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_C0CF65F6B03A8386 ON emplacement (created_by_id)');
        $this->addSql('CREATE INDEX IDX_C0CF65F6896DBBDE ON emplacement (updated_by_id)');
        $this->addSql('ALTER TABLE ligne_commande ADD created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, ADD updated_at DATETIME DEFAULT NULL, ADD created_by_id INT DEFAULT NULL, ADD updated_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ligne_commande ADD CONSTRAINT FK_3170B74BB03A8386 FOREIGN KEY (created_by_id) REFERENCES utilisateur (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE ligne_commande ADD CONSTRAINT FK_3170B74B896DBBDE FOREIGN KEY (updated_by_id) REFERENCES utilisateur (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_3170B74BB03A8386 ON ligne_commande (created_by_id)');
        $this->addSql('CREATE INDEX IDX_3170B74B896DBBDE ON ligne_commande (updated_by_id)');
        $this->addSql('ALTER TABLE ligne_reception ADD created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, ADD updated_at DATETIME DEFAULT NULL, ADD created_by_id INT DEFAULT NULL, ADD updated_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ligne_reception ADD CONSTRAINT FK_9F338AA7B03A8386 FOREIGN KEY (created_by_id) REFERENCES utilisateur (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE ligne_reception ADD CONSTRAINT FK_9F338AA7896DBBDE FOREIGN KEY (updated_by_id) REFERENCES utilisateur (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_9F338AA7B03A8386 ON ligne_reception (created_by_id)');
        $this->addSql('CREATE INDEX IDX_9F338AA7896DBBDE ON ligne_reception (updated_by_id)');
        $this->addSql('ALTER TABLE reception ADD validated_at DATETIME DEFAULT NULL, ADD created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, ADD updated_at DATETIME DEFAULT NULL, ADD validated_by_id INT DEFAULT NULL, ADD created_by_id INT DEFAULT NULL, ADD updated_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE reception ADD CONSTRAINT FK_50D6852FC69DE5E5 FOREIGN KEY (validated_by_id) REFERENCES utilisateur (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE reception ADD CONSTRAINT FK_50D6852FB03A8386 FOREIGN KEY (created_by_id) REFERENCES utilisateur (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE reception ADD CONSTRAINT FK_50D6852F896DBBDE FOREIGN KEY (updated_by_id) REFERENCES utilisateur (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_50D6852FC69DE5E5 ON reception (validated_by_id)');
        $this->addSql('CREATE INDEX IDX_50D6852FB03A8386 ON reception (created_by_id)');
        $this->addSql('CREATE INDEX IDX_50D6852F896DBBDE ON reception (updated_by_id)');
        $this->addSql('ALTER TABLE stock ADD created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, ADD updated_at DATETIME DEFAULT NULL, ADD created_by_id INT DEFAULT NULL, ADD updated_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE stock ADD CONSTRAINT FK_4B365660B03A8386 FOREIGN KEY (created_by_id) REFERENCES utilisateur (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE stock ADD CONSTRAINT FK_4B365660896DBBDE FOREIGN KEY (updated_by_id) REFERENCES utilisateur (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_4B365660B03A8386 ON stock (created_by_id)');
        $this->addSql('CREATE INDEX IDX_4B365660896DBBDE ON stock (updated_by_id)');
        $this->addSql('ALTER TABLE tiers ADD created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, ADD updated_at DATETIME DEFAULT NULL, ADD created_by_id INT DEFAULT NULL, ADD updated_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tiers ADD CONSTRAINT FK_16473BA2B03A8386 FOREIGN KEY (created_by_id) REFERENCES utilisateur (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE tiers ADD CONSTRAINT FK_16473BA2896DBBDE FOREIGN KEY (updated_by_id) REFERENCES utilisateur (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_16473BA2B03A8386 ON tiers (created_by_id)');
        $this->addSql('CREATE INDEX IDX_16473BA2896DBBDE ON tiers (updated_by_id)');
        $this->addSql('ALTER TABLE transfert_emplacement ADD created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, ADD updated_at DATETIME DEFAULT NULL, ADD created_by_id INT DEFAULT NULL, ADD updated_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE transfert_emplacement ADD CONSTRAINT FK_8418AC9DB03A8386 FOREIGN KEY (created_by_id) REFERENCES utilisateur (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE transfert_emplacement ADD CONSTRAINT FK_8418AC9D896DBBDE FOREIGN KEY (updated_by_id) REFERENCES utilisateur (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_8418AC9DB03A8386 ON transfert_emplacement (created_by_id)');
        $this->addSql('CREATE INDEX IDX_8418AC9D896DBBDE ON transfert_emplacement (updated_by_id)');
        $this->addSql('ALTER TABLE type_conditionnement ADD created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, ADD updated_at DATETIME DEFAULT NULL, ADD created_by_id INT DEFAULT NULL, ADD updated_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE type_conditionnement ADD CONSTRAINT FK_C87F7E4EB03A8386 FOREIGN KEY (created_by_id) REFERENCES utilisateur (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE type_conditionnement ADD CONSTRAINT FK_C87F7E4E896DBBDE FOREIGN KEY (updated_by_id) REFERENCES utilisateur (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_C87F7E4EB03A8386 ON type_conditionnement (created_by_id)');
        $this->addSql('CREATE INDEX IDX_C87F7E4E896DBBDE ON type_conditionnement (updated_by_id)');
        $this->addSql('ALTER TABLE type_emplacement ADD created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, ADD updated_at DATETIME DEFAULT NULL, ADD created_by_id INT DEFAULT NULL, ADD updated_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE type_emplacement ADD CONSTRAINT FK_8BD6B267B03A8386 FOREIGN KEY (created_by_id) REFERENCES utilisateur (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE type_emplacement ADD CONSTRAINT FK_8BD6B267896DBBDE FOREIGN KEY (updated_by_id) REFERENCES utilisateur (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_8BD6B267B03A8386 ON type_emplacement (created_by_id)');
        $this->addSql('CREATE INDEX IDX_8BD6B267896DBBDE ON type_emplacement (updated_by_id)');
        $this->addSql('ALTER TABLE utilisateur ADD created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, ADD updated_at DATETIME DEFAULT NULL, ADD created_by_id INT DEFAULT NULL, ADD updated_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE utilisateur ADD CONSTRAINT FK_1D1C63B3B03A8386 FOREIGN KEY (created_by_id) REFERENCES utilisateur (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE utilisateur ADD CONSTRAINT FK_1D1C63B3896DBBDE FOREIGN KEY (updated_by_id) REFERENCES utilisateur (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_1D1C63B3B03A8386 ON utilisateur (created_by_id)');
        $this->addSql('CREATE INDEX IDX_1D1C63B3896DBBDE ON utilisateur (updated_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E66B03A8386');
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E66896DBBDE');
        $this->addSql('DROP INDEX IDX_23A0E66B03A8386 ON article');
        $this->addSql('DROP INDEX IDX_23A0E66896DBBDE ON article');
        $this->addSql('ALTER TABLE article DROP created_at, DROP updated_at, DROP created_by_id, DROP updated_by_id');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DB03A8386');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67D896DBBDE');
        $this->addSql('DROP INDEX IDX_6EEAA67DB03A8386 ON commande');
        $this->addSql('DROP INDEX IDX_6EEAA67D896DBBDE ON commande');
        $this->addSql('ALTER TABLE commande DROP created_at, DROP updated_at, DROP created_by_id, DROP updated_by_id');
        $this->addSql('ALTER TABLE emplacement DROP FOREIGN KEY FK_C0CF65F6B03A8386');
        $this->addSql('ALTER TABLE emplacement DROP FOREIGN KEY FK_C0CF65F6896DBBDE');
        $this->addSql('DROP INDEX IDX_C0CF65F6B03A8386 ON emplacement');
        $this->addSql('DROP INDEX IDX_C0CF65F6896DBBDE ON emplacement');
        $this->addSql('ALTER TABLE emplacement DROP created_at, DROP updated_at, DROP created_by_id, DROP updated_by_id');
        $this->addSql('ALTER TABLE ligne_commande DROP FOREIGN KEY FK_3170B74BB03A8386');
        $this->addSql('ALTER TABLE ligne_commande DROP FOREIGN KEY FK_3170B74B896DBBDE');
        $this->addSql('DROP INDEX IDX_3170B74BB03A8386 ON ligne_commande');
        $this->addSql('DROP INDEX IDX_3170B74B896DBBDE ON ligne_commande');
        $this->addSql('ALTER TABLE ligne_commande DROP created_at, DROP updated_at, DROP created_by_id, DROP updated_by_id');
        $this->addSql('ALTER TABLE ligne_reception DROP FOREIGN KEY FK_9F338AA7B03A8386');
        $this->addSql('ALTER TABLE ligne_reception DROP FOREIGN KEY FK_9F338AA7896DBBDE');
        $this->addSql('DROP INDEX IDX_9F338AA7B03A8386 ON ligne_reception');
        $this->addSql('DROP INDEX IDX_9F338AA7896DBBDE ON ligne_reception');
        $this->addSql('ALTER TABLE ligne_reception DROP created_at, DROP updated_at, DROP created_by_id, DROP updated_by_id');
        $this->addSql('ALTER TABLE reception DROP FOREIGN KEY FK_50D6852FC69DE5E5');
        $this->addSql('ALTER TABLE reception DROP FOREIGN KEY FK_50D6852FB03A8386');
        $this->addSql('ALTER TABLE reception DROP FOREIGN KEY FK_50D6852F896DBBDE');
        $this->addSql('DROP INDEX IDX_50D6852FC69DE5E5 ON reception');
        $this->addSql('DROP INDEX IDX_50D6852FB03A8386 ON reception');
        $this->addSql('DROP INDEX IDX_50D6852F896DBBDE ON reception');
        $this->addSql('ALTER TABLE reception DROP validated_at, DROP created_at, DROP updated_at, DROP validated_by_id, DROP created_by_id, DROP updated_by_id');
        $this->addSql('ALTER TABLE stock DROP FOREIGN KEY FK_4B365660B03A8386');
        $this->addSql('ALTER TABLE stock DROP FOREIGN KEY FK_4B365660896DBBDE');
        $this->addSql('DROP INDEX IDX_4B365660B03A8386 ON stock');
        $this->addSql('DROP INDEX IDX_4B365660896DBBDE ON stock');
        $this->addSql('ALTER TABLE stock DROP created_at, DROP updated_at, DROP created_by_id, DROP updated_by_id');
        $this->addSql('ALTER TABLE tiers DROP FOREIGN KEY FK_16473BA2B03A8386');
        $this->addSql('ALTER TABLE tiers DROP FOREIGN KEY FK_16473BA2896DBBDE');
        $this->addSql('DROP INDEX IDX_16473BA2B03A8386 ON tiers');
        $this->addSql('DROP INDEX IDX_16473BA2896DBBDE ON tiers');
        $this->addSql('ALTER TABLE tiers DROP created_at, DROP updated_at, DROP created_by_id, DROP updated_by_id');
        $this->addSql('ALTER TABLE transfert_emplacement DROP FOREIGN KEY FK_8418AC9DB03A8386');
        $this->addSql('ALTER TABLE transfert_emplacement DROP FOREIGN KEY FK_8418AC9D896DBBDE');
        $this->addSql('DROP INDEX IDX_8418AC9DB03A8386 ON transfert_emplacement');
        $this->addSql('DROP INDEX IDX_8418AC9D896DBBDE ON transfert_emplacement');
        $this->addSql('ALTER TABLE transfert_emplacement DROP created_at, DROP updated_at, DROP created_by_id, DROP updated_by_id');
        $this->addSql('ALTER TABLE type_conditionnement DROP FOREIGN KEY FK_C87F7E4EB03A8386');
        $this->addSql('ALTER TABLE type_conditionnement DROP FOREIGN KEY FK_C87F7E4E896DBBDE');
        $this->addSql('DROP INDEX IDX_C87F7E4EB03A8386 ON type_conditionnement');
        $this->addSql('DROP INDEX IDX_C87F7E4E896DBBDE ON type_conditionnement');
        $this->addSql('ALTER TABLE type_conditionnement DROP created_at, DROP updated_at, DROP created_by_id, DROP updated_by_id');
        $this->addSql('ALTER TABLE type_emplacement DROP FOREIGN KEY FK_8BD6B267B03A8386');
        $this->addSql('ALTER TABLE type_emplacement DROP FOREIGN KEY FK_8BD6B267896DBBDE');
        $this->addSql('DROP INDEX IDX_8BD6B267B03A8386 ON type_emplacement');
        $this->addSql('DROP INDEX IDX_8BD6B267896DBBDE ON type_emplacement');
        $this->addSql('ALTER TABLE type_emplacement DROP created_at, DROP updated_at, DROP created_by_id, DROP updated_by_id');
        $this->addSql('ALTER TABLE utilisateur DROP FOREIGN KEY FK_1D1C63B3B03A8386');
        $this->addSql('ALTER TABLE utilisateur DROP FOREIGN KEY FK_1D1C63B3896DBBDE');
        $this->addSql('DROP INDEX IDX_1D1C63B3B03A8386 ON utilisateur');
        $this->addSql('DROP INDEX IDX_1D1C63B3896DBBDE ON utilisateur');
        $this->addSql('ALTER TABLE utilisateur DROP created_at, DROP updated_at, DROP created_by_id, DROP updated_by_id');
    }
}
