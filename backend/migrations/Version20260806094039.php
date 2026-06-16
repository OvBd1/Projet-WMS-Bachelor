<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260806094039 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Multi-tenance par dossier : table dossier + colonne dossier_id sur les entites metier, backfill sur un dossier par defaut.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE dossier (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(50) NOT NULL, raison_sociale VARCHAR(255) NOT NULL, rue VARCHAR(255) DEFAULT NULL, code_postal VARCHAR(20) DEFAULT NULL, ville VARCHAR(255) DEFAULT NULL, pays VARCHAR(100) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_3D48E03777153098 (code), INDEX IDX_3D48E037B03A8386 (created_by_id), INDEX IDX_3D48E037896DBBDE (updated_by_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE dossier ADD CONSTRAINT FK_3D48E037B03A8386 FOREIGN KEY (created_by_id) REFERENCES utilisateur (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE dossier ADD CONSTRAINT FK_3D48E037896DBBDE FOREIGN KEY (updated_by_id) REFERENCES utilisateur (id) ON DELETE SET NULL');

        $this->addSql("INSERT INTO dossier (code, raison_sociale, created_at, created_by_id) VALUES ('DOS1', 'Entreprise par defaut', NOW(), 1)");

        $this->addSql('DROP INDEX UNIQ_23A0E66AEA34913 ON article');
        $this->addSql('ALTER TABLE article ADD dossier_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E66611C0C56 FOREIGN KEY (dossier_id) REFERENCES dossier (id)');
        $this->addSql('CREATE INDEX IDX_23A0E66611C0C56 ON article (dossier_id)');
        $this->addSql("UPDATE article SET dossier_id = (SELECT id FROM dossier WHERE code = 'DOS1') WHERE dossier_id IS NULL");
        $this->addSql('ALTER TABLE article MODIFY dossier_id INT NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX uq_article_dossier_reference ON article (dossier_id, reference)');

        $this->addSql('ALTER TABLE commande ADD dossier_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67D611C0C56 FOREIGN KEY (dossier_id) REFERENCES dossier (id)');
        $this->addSql('CREATE INDEX IDX_6EEAA67D611C0C56 ON commande (dossier_id)');
        $this->addSql("UPDATE commande SET dossier_id = (SELECT id FROM dossier WHERE code = 'DOS1') WHERE dossier_id IS NULL");
        $this->addSql('ALTER TABLE commande MODIFY dossier_id INT NOT NULL');

        $this->addSql('DROP INDEX UNIQ_C0CF65F677153098 ON emplacement');
        $this->addSql('ALTER TABLE emplacement ADD dossier_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE emplacement ADD CONSTRAINT FK_C0CF65F6611C0C56 FOREIGN KEY (dossier_id) REFERENCES dossier (id)');
        $this->addSql('CREATE INDEX IDX_C0CF65F6611C0C56 ON emplacement (dossier_id)');
        $this->addSql("UPDATE emplacement SET dossier_id = (SELECT id FROM dossier WHERE code = 'DOS1') WHERE dossier_id IS NULL");
        $this->addSql('ALTER TABLE emplacement MODIFY dossier_id INT NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX uq_emplacement_dossier_code ON emplacement (dossier_id, code)');

        $this->addSql('ALTER TABLE reception ADD dossier_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE reception ADD CONSTRAINT FK_50D6852F611C0C56 FOREIGN KEY (dossier_id) REFERENCES dossier (id)');
        $this->addSql('CREATE INDEX IDX_50D6852F611C0C56 ON reception (dossier_id)');
        $this->addSql("UPDATE reception SET dossier_id = (SELECT id FROM dossier WHERE code = 'DOS1') WHERE dossier_id IS NULL");
        $this->addSql('ALTER TABLE reception MODIFY dossier_id INT NOT NULL');

        $this->addSql('ALTER TABLE stock ADD dossier_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE stock ADD CONSTRAINT FK_4B365660611C0C56 FOREIGN KEY (dossier_id) REFERENCES dossier (id)');
        $this->addSql('CREATE INDEX IDX_4B365660611C0C56 ON stock (dossier_id)');
        $this->addSql("UPDATE stock SET dossier_id = (SELECT id FROM dossier WHERE code = 'DOS1') WHERE dossier_id IS NULL");
        $this->addSql('ALTER TABLE stock MODIFY dossier_id INT NOT NULL');

        $this->addSql('DROP INDEX UNIQ_16473BA277153098 ON tiers');
        $this->addSql('ALTER TABLE tiers ADD dossier_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tiers ADD CONSTRAINT FK_16473BA2611C0C56 FOREIGN KEY (dossier_id) REFERENCES dossier (id)');
        $this->addSql('CREATE INDEX IDX_16473BA2611C0C56 ON tiers (dossier_id)');
        $this->addSql("UPDATE tiers SET dossier_id = (SELECT id FROM dossier WHERE code = 'DOS1') WHERE dossier_id IS NULL");
        $this->addSql('ALTER TABLE tiers MODIFY dossier_id INT NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX uq_tiers_dossier_code ON tiers (dossier_id, code)');

        $this->addSql('ALTER TABLE transfert_emplacement ADD dossier_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE transfert_emplacement ADD CONSTRAINT FK_8418AC9D611C0C56 FOREIGN KEY (dossier_id) REFERENCES dossier (id)');
        $this->addSql('CREATE INDEX IDX_8418AC9D611C0C56 ON transfert_emplacement (dossier_id)');
        $this->addSql("UPDATE transfert_emplacement SET dossier_id = (SELECT id FROM dossier WHERE code = 'DOS1') WHERE dossier_id IS NULL");
        $this->addSql('ALTER TABLE transfert_emplacement MODIFY dossier_id INT NOT NULL');

        $this->addSql('ALTER TABLE type_conditionnement ADD dossier_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE type_conditionnement ADD CONSTRAINT FK_C87F7E4E611C0C56 FOREIGN KEY (dossier_id) REFERENCES dossier (id)');
        $this->addSql('CREATE INDEX IDX_C87F7E4E611C0C56 ON type_conditionnement (dossier_id)');
        $this->addSql("UPDATE type_conditionnement SET dossier_id = (SELECT id FROM dossier WHERE code = 'DOS1') WHERE dossier_id IS NULL");
        $this->addSql('ALTER TABLE type_conditionnement MODIFY dossier_id INT NOT NULL');

        $this->addSql('ALTER TABLE type_emplacement ADD dossier_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE type_emplacement ADD CONSTRAINT FK_8BD6B267611C0C56 FOREIGN KEY (dossier_id) REFERENCES dossier (id)');
        $this->addSql('CREATE INDEX IDX_8BD6B267611C0C56 ON type_emplacement (dossier_id)');
        $this->addSql("UPDATE type_emplacement SET dossier_id = (SELECT id FROM dossier WHERE code = 'DOS1') WHERE dossier_id IS NULL");
        $this->addSql('ALTER TABLE type_emplacement MODIFY dossier_id INT NOT NULL');

        $this->addSql('ALTER TABLE utilisateur ADD dossier_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE utilisateur ADD CONSTRAINT FK_1D1C63B3611C0C56 FOREIGN KEY (dossier_id) REFERENCES dossier (id)');
        $this->addSql('CREATE INDEX IDX_1D1C63B3611C0C56 ON utilisateur (dossier_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dossier DROP FOREIGN KEY FK_3D48E037B03A8386');
        $this->addSql('ALTER TABLE dossier DROP FOREIGN KEY FK_3D48E037896DBBDE');
        $this->addSql('DROP TABLE dossier');
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E66611C0C56');
        $this->addSql('DROP INDEX IDX_23A0E66611C0C56 ON article');
        $this->addSql('DROP INDEX uq_article_dossier_reference ON article');
        $this->addSql('ALTER TABLE article DROP dossier_id');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_23A0E66AEA34913 ON article (reference)');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67D611C0C56');
        $this->addSql('DROP INDEX IDX_6EEAA67D611C0C56 ON commande');
        $this->addSql('ALTER TABLE commande DROP dossier_id');
        $this->addSql('ALTER TABLE emplacement DROP FOREIGN KEY FK_C0CF65F6611C0C56');
        $this->addSql('DROP INDEX IDX_C0CF65F6611C0C56 ON emplacement');
        $this->addSql('DROP INDEX uq_emplacement_dossier_code ON emplacement');
        $this->addSql('ALTER TABLE emplacement DROP dossier_id');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C0CF65F677153098 ON emplacement (code)');
        $this->addSql('ALTER TABLE reception DROP FOREIGN KEY FK_50D6852F611C0C56');
        $this->addSql('DROP INDEX IDX_50D6852F611C0C56 ON reception');
        $this->addSql('ALTER TABLE reception DROP dossier_id');
        $this->addSql('ALTER TABLE stock DROP FOREIGN KEY FK_4B365660611C0C56');
        $this->addSql('DROP INDEX IDX_4B365660611C0C56 ON stock');
        $this->addSql('ALTER TABLE stock DROP dossier_id');
        $this->addSql('ALTER TABLE tiers DROP FOREIGN KEY FK_16473BA2611C0C56');
        $this->addSql('DROP INDEX IDX_16473BA2611C0C56 ON tiers');
        $this->addSql('DROP INDEX uq_tiers_dossier_code ON tiers');
        $this->addSql('ALTER TABLE tiers DROP dossier_id');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_16473BA277153098 ON tiers (code)');
        $this->addSql('ALTER TABLE transfert_emplacement DROP FOREIGN KEY FK_8418AC9D611C0C56');
        $this->addSql('DROP INDEX IDX_8418AC9D611C0C56 ON transfert_emplacement');
        $this->addSql('ALTER TABLE transfert_emplacement DROP dossier_id');
        $this->addSql('ALTER TABLE type_conditionnement DROP FOREIGN KEY FK_C87F7E4E611C0C56');
        $this->addSql('DROP INDEX IDX_C87F7E4E611C0C56 ON type_conditionnement');
        $this->addSql('ALTER TABLE type_conditionnement DROP dossier_id');
        $this->addSql('ALTER TABLE type_emplacement DROP FOREIGN KEY FK_8BD6B267611C0C56');
        $this->addSql('DROP INDEX IDX_8BD6B267611C0C56 ON type_emplacement');
        $this->addSql('ALTER TABLE type_emplacement DROP dossier_id');
        $this->addSql('ALTER TABLE utilisateur DROP FOREIGN KEY FK_1D1C63B3611C0C56');
        $this->addSql('DROP INDEX IDX_1D1C63B3611C0C56 ON utilisateur');
        $this->addSql('ALTER TABLE utilisateur DROP dossier_id');
    }
}
