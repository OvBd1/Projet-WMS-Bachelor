<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260601084539 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE article (id INT AUTO_INCREMENT NOT NULL, reference VARCHAR(50) NOT NULL, libelle VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_23A0E66AEA34913 (reference), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE commande (id INT AUTO_INCREMENT NOT NULL, date_commande DATETIME NOT NULL, statut VARCHAR(20) NOT NULL, utilisateur_id INT NOT NULL, INDEX IDX_6EEAA67DFB88E14F (utilisateur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE emplacement (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(50) NOT NULL, description LONGTEXT DEFAULT NULL, type_emplacement_id INT NOT NULL, UNIQUE INDEX UNIQ_C0CF65F677153098 (code), INDEX IDX_C0CF65F6929AA3C5 (type_emplacement_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE ligne_commande (id INT AUTO_INCREMENT NOT NULL, quantite INT NOT NULL, commande_id INT NOT NULL, article_id INT NOT NULL, INDEX IDX_3170B74B82EA2E54 (commande_id), INDEX IDX_3170B74B7294869C (article_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE ligne_reception (id INT AUTO_INCREMENT NOT NULL, quantite INT NOT NULL, reception_id INT NOT NULL, article_id INT NOT NULL, emplacement_id INT NOT NULL, INDEX IDX_9F338AA77C14DF52 (reception_id), INDEX IDX_9F338AA77294869C (article_id), INDEX IDX_9F338AA7C4598A51 (emplacement_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE reception (id INT AUTO_INCREMENT NOT NULL, date_reception DATETIME NOT NULL, utilisateur_id INT NOT NULL, INDEX IDX_50D6852FFB88E14F (utilisateur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE stock (id INT AUTO_INCREMENT NOT NULL, quantite INT NOT NULL, article_id INT NOT NULL, emplacement_id INT NOT NULL, INDEX IDX_4B3656607294869C (article_id), INDEX IDX_4B365660C4598A51 (emplacement_id), UNIQUE INDEX uq_stock_article_emplacement (article_id, emplacement_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE transfert_emplacement (id INT AUTO_INCREMENT NOT NULL, date_transfert DATETIME NOT NULL, quantite INT NOT NULL, utilisateur_id INT NOT NULL, article_id INT NOT NULL, emplacement_source_id INT NOT NULL, emplacement_destination_id INT NOT NULL, INDEX IDX_8418AC9DFB88E14F (utilisateur_id), INDEX IDX_8418AC9D7294869C (article_id), INDEX IDX_8418AC9DBC9D4F59 (emplacement_source_id), INDEX IDX_8418AC9D55799AF7 (emplacement_destination_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE type_emplacement (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(100) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, role VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_1D1C63B3E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE emplacement ADD CONSTRAINT FK_C0CF65F6929AA3C5 FOREIGN KEY (type_emplacement_id) REFERENCES type_emplacement (id)');
        $this->addSql('ALTER TABLE ligne_commande ADD CONSTRAINT FK_3170B74B82EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id)');
        $this->addSql('ALTER TABLE ligne_commande ADD CONSTRAINT FK_3170B74B7294869C FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('ALTER TABLE ligne_reception ADD CONSTRAINT FK_9F338AA77C14DF52 FOREIGN KEY (reception_id) REFERENCES reception (id)');
        $this->addSql('ALTER TABLE ligne_reception ADD CONSTRAINT FK_9F338AA77294869C FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('ALTER TABLE ligne_reception ADD CONSTRAINT FK_9F338AA7C4598A51 FOREIGN KEY (emplacement_id) REFERENCES emplacement (id)');
        $this->addSql('ALTER TABLE reception ADD CONSTRAINT FK_50D6852FFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE stock ADD CONSTRAINT FK_4B3656607294869C FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('ALTER TABLE stock ADD CONSTRAINT FK_4B365660C4598A51 FOREIGN KEY (emplacement_id) REFERENCES emplacement (id)');
        $this->addSql('ALTER TABLE transfert_emplacement ADD CONSTRAINT FK_8418AC9DFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE transfert_emplacement ADD CONSTRAINT FK_8418AC9D7294869C FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('ALTER TABLE transfert_emplacement ADD CONSTRAINT FK_8418AC9DBC9D4F59 FOREIGN KEY (emplacement_source_id) REFERENCES emplacement (id)');
        $this->addSql('ALTER TABLE transfert_emplacement ADD CONSTRAINT FK_8418AC9D55799AF7 FOREIGN KEY (emplacement_destination_id) REFERENCES emplacement (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DFB88E14F');
        $this->addSql('ALTER TABLE emplacement DROP FOREIGN KEY FK_C0CF65F6929AA3C5');
        $this->addSql('ALTER TABLE ligne_commande DROP FOREIGN KEY FK_3170B74B82EA2E54');
        $this->addSql('ALTER TABLE ligne_commande DROP FOREIGN KEY FK_3170B74B7294869C');
        $this->addSql('ALTER TABLE ligne_reception DROP FOREIGN KEY FK_9F338AA77C14DF52');
        $this->addSql('ALTER TABLE ligne_reception DROP FOREIGN KEY FK_9F338AA77294869C');
        $this->addSql('ALTER TABLE ligne_reception DROP FOREIGN KEY FK_9F338AA7C4598A51');
        $this->addSql('ALTER TABLE reception DROP FOREIGN KEY FK_50D6852FFB88E14F');
        $this->addSql('ALTER TABLE stock DROP FOREIGN KEY FK_4B3656607294869C');
        $this->addSql('ALTER TABLE stock DROP FOREIGN KEY FK_4B365660C4598A51');
        $this->addSql('ALTER TABLE transfert_emplacement DROP FOREIGN KEY FK_8418AC9DFB88E14F');
        $this->addSql('ALTER TABLE transfert_emplacement DROP FOREIGN KEY FK_8418AC9D7294869C');
        $this->addSql('ALTER TABLE transfert_emplacement DROP FOREIGN KEY FK_8418AC9DBC9D4F59');
        $this->addSql('ALTER TABLE transfert_emplacement DROP FOREIGN KEY FK_8418AC9D55799AF7');
        $this->addSql('DROP TABLE article');
        $this->addSql('DROP TABLE commande');
        $this->addSql('DROP TABLE emplacement');
        $this->addSql('DROP TABLE ligne_commande');
        $this->addSql('DROP TABLE ligne_reception');
        $this->addSql('DROP TABLE reception');
        $this->addSql('DROP TABLE stock');
        $this->addSql('DROP TABLE transfert_emplacement');
        $this->addSql('DROP TABLE type_emplacement');
        $this->addSql('DROP TABLE utilisateur');
    }
}
