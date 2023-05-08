<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230503124138 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mothly_yield ADD device_id INT DEFAULT NULL, ADD serial_number INT NOT NULL, ADD start_date DATETIME NOT NULL, ADD end_date DATETIME NOT NULL, ADD yield DOUBLE PRECISION NOT NULL, ADD surplus DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE mothly_yield ADD CONSTRAINT FK_6A16E71194A4C7D4 FOREIGN KEY (device_id) REFERENCES devices (id)');
        $this->addSql('CREATE INDEX IDX_6A16E71194A4C7D4 ON mothly_yield (device_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mothly_yield DROP FOREIGN KEY FK_6A16E71194A4C7D4');
        $this->addSql('DROP INDEX IDX_6A16E71194A4C7D4 ON mothly_yield');
        $this->addSql('ALTER TABLE mothly_yield DROP device_id, DROP serial_number, DROP start_date, DROP end_date, DROP yield, DROP surplus');
    }
}
