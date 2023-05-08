<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230504114106 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE devices DROP FOREIGN KEY FK_11074E9AB171EB6C');
        $this->addSql('ALTER TABLE elec_contracts DROP FOREIGN KEY FK_930D701FB171EB6C');
        $this->addSql('ALTER TABLE mothly_yield DROP FOREIGN KEY FK_6A16E71194A4C7D4');
        $this->addSql('ALTER TABLE quarter_yield DROP FOREIGN KEY FK_7B15FC0A94A4C7D4');
        $this->addSql('ALTER TABLE yearly_yield DROP FOREIGN KEY FK_7FCEEB5594A4C7D4');
        $this->addSql('DROP TABLE customer');
        $this->addSql('DROP TABLE devices');
        $this->addSql('DROP TABLE elec_contracts');
        $this->addSql('DROP TABLE mothly_yield');
        $this->addSql('DROP TABLE quarter_yield');
        $this->addSql('DROP TABLE test_db');
        $this->addSql('DROP TABLE yearly_yield');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE customer (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, last_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, email VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, phonenumber VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, postcode VARCHAR(6) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, city VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, street VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, house_number VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, bank_acount VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE devices (id INT AUTO_INCREMENT NOT NULL, customer_id_id INT NOT NULL, serial_number VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, status VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, type VARCHAR(5) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_11074E9AB171EB6C (customer_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE elec_contracts (id INT AUTO_INCREMENT NOT NULL, customer_id_id INT NOT NULL, start_date DATETIME NOT NULL, end_date DATETIME NOT NULL, sell_price INT NOT NULL, buy_price INT NOT NULL, INDEX IDX_930D701FB171EB6C (customer_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE mothly_yield (id INT AUTO_INCREMENT NOT NULL, device_id INT DEFAULT NULL, serial_number INT NOT NULL, start_date DATETIME NOT NULL, end_date DATETIME NOT NULL, yield DOUBLE PRECISION NOT NULL, surplus DOUBLE PRECISION NOT NULL, INDEX IDX_6A16E71194A4C7D4 (device_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE quarter_yield (id INT AUTO_INCREMENT NOT NULL, device_id INT NOT NULL, serial_number INT NOT NULL, start_date DATETIME NOT NULL, end_date DATETIME NOT NULL, yield DOUBLE PRECISION NOT NULL, surplus DOUBLE PRECISION NOT NULL, INDEX IDX_7B15FC0A94A4C7D4 (device_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE test_db (id INT AUTO_INCREMENT NOT NULL, ran VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, do VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE yearly_yield (id INT AUTO_INCREMENT NOT NULL, device_id INT NOT NULL, serial_number INT NOT NULL, start_date DATETIME NOT NULL, end_date DATETIME NOT NULL, yield DOUBLE PRECISION NOT NULL, surplus DOUBLE PRECISION NOT NULL, INDEX IDX_7FCEEB5594A4C7D4 (device_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE devices ADD CONSTRAINT FK_11074E9AB171EB6C FOREIGN KEY (customer_id_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE elec_contracts ADD CONSTRAINT FK_930D701FB171EB6C FOREIGN KEY (customer_id_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE mothly_yield ADD CONSTRAINT FK_6A16E71194A4C7D4 FOREIGN KEY (device_id) REFERENCES devices (id)');
        $this->addSql('ALTER TABLE quarter_yield ADD CONSTRAINT FK_7B15FC0A94A4C7D4 FOREIGN KEY (device_id) REFERENCES devices (id)');
        $this->addSql('ALTER TABLE yearly_yield ADD CONSTRAINT FK_7FCEEB5594A4C7D4 FOREIGN KEY (device_id) REFERENCES devices (id)');
    }
}
