<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230503113458 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE devices (id INT AUTO_INCREMENT NOT NULL, customer_id_id INT NOT NULL, serial_number VARCHAR(10) NOT NULL, status VARCHAR(10) NOT NULL, INDEX IDX_11074E9AB171EB6C (customer_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE elec_contracts (id INT AUTO_INCREMENT NOT NULL, customer_id_id INT NOT NULL, start_date DATETIME NOT NULL, end_date DATETIME NOT NULL, sell_price INT NOT NULL, buy_price INT NOT NULL, INDEX IDX_930D701FB171EB6C (customer_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE devices ADD CONSTRAINT FK_11074E9AB171EB6C FOREIGN KEY (customer_id_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE elec_contracts ADD CONSTRAINT FK_930D701FB171EB6C FOREIGN KEY (customer_id_id) REFERENCES customer (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE devices DROP FOREIGN KEY FK_11074E9AB171EB6C');
        $this->addSql('ALTER TABLE elec_contracts DROP FOREIGN KEY FK_930D701FB171EB6C');
        $this->addSql('DROP TABLE devices');
        $this->addSql('DROP TABLE elec_contracts');
    }
}
