<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230504115725 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contract (id INT AUTO_INCREMENT NOT NULL, customer_id INT NOT NULL, start_date DATETIME NOT NULL, end_date DATETIME NOT NULL, sell_price INT NOT NULL, buy_price INT NOT NULL, INDEX IDX_E98F28599395C3F3 (customer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE customer (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) DEFAULT NULL, email VARCHAR(255) NOT NULL, phonenumber VARCHAR(20) NOT NULL, postcode VARCHAR(6) NOT NULL, city VARCHAR(255) NOT NULL, street VARCHAR(255) NOT NULL, house_number VARCHAR(100) NOT NULL, bank_account VARCHAR(40) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE devices (id INT AUTO_INCREMENT NOT NULL, customer_id INT NOT NULL, serial_number INT NOT NULL, status VARCHAR(12) NOT NULL, type VARCHAR(8) NOT NULL, INDEX IDX_11074E9A9395C3F3 (customer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mothly_yield (id INT AUTO_INCREMENT NOT NULL, device_id INT NOT NULL, serial_number INT NOT NULL, start_date DATETIME NOT NULL, end_date DATETIME NOT NULL, yield DOUBLE PRECISION NOT NULL, surplus DOUBLE PRECISION NOT NULL, INDEX IDX_6A16E71194A4C7D4 (device_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE quarter_yield (id INT AUTO_INCREMENT NOT NULL, device_id INT NOT NULL, serial_number INT NOT NULL, start_date DATETIME NOT NULL, end_date DATETIME NOT NULL, yield DOUBLE PRECISION NOT NULL, surplus DOUBLE PRECISION NOT NULL, INDEX IDX_7B15FC0A94A4C7D4 (device_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE yearly_yield (id INT AUTO_INCREMENT NOT NULL, device_id INT NOT NULL, serial_number INT NOT NULL, start_date DATETIME NOT NULL, end_date DATETIME NOT NULL, yield DOUBLE PRECISION NOT NULL, surplus DOUBLE PRECISION NOT NULL, INDEX IDX_7FCEEB5594A4C7D4 (device_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE contract ADD CONSTRAINT FK_E98F28599395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE devices ADD CONSTRAINT FK_11074E9A9395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE mothly_yield ADD CONSTRAINT FK_6A16E71194A4C7D4 FOREIGN KEY (device_id) REFERENCES devices (id)');
        $this->addSql('ALTER TABLE quarter_yield ADD CONSTRAINT FK_7B15FC0A94A4C7D4 FOREIGN KEY (device_id) REFERENCES devices (id)');
        $this->addSql('ALTER TABLE yearly_yield ADD CONSTRAINT FK_7FCEEB5594A4C7D4 FOREIGN KEY (device_id) REFERENCES devices (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contract DROP FOREIGN KEY FK_E98F28599395C3F3');
        $this->addSql('ALTER TABLE devices DROP FOREIGN KEY FK_11074E9A9395C3F3');
        $this->addSql('ALTER TABLE mothly_yield DROP FOREIGN KEY FK_6A16E71194A4C7D4');
        $this->addSql('ALTER TABLE quarter_yield DROP FOREIGN KEY FK_7B15FC0A94A4C7D4');
        $this->addSql('ALTER TABLE yearly_yield DROP FOREIGN KEY FK_7FCEEB5594A4C7D4');
        $this->addSql('DROP TABLE contract');
        $this->addSql('DROP TABLE customer');
        $this->addSql('DROP TABLE devices');
        $this->addSql('DROP TABLE mothly_yield');
        $this->addSql('DROP TABLE quarter_yield');
        $this->addSql('DROP TABLE yearly_yield');
    }
}
