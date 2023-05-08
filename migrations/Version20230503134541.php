<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230503134541 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE yearly_yield (id INT AUTO_INCREMENT NOT NULL, device_id INT NOT NULL, serial_number INT NOT NULL, start_date DATETIME NOT NULL, end_date DATETIME NOT NULL, yield DOUBLE PRECISION NOT NULL, surplus DOUBLE PRECISION NOT NULL, INDEX IDX_7FCEEB5594A4C7D4 (device_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE yearly_yield ADD CONSTRAINT FK_7FCEEB5594A4C7D4 FOREIGN KEY (device_id) REFERENCES devices (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE yearly_yield DROP FOREIGN KEY FK_7FCEEB5594A4C7D4');
        $this->addSql('DROP TABLE yearly_yield');
    }
}
