<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230523130149 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX pc ON pc_lat_long');
        $this->addSql('ALTER TABLE pc_lat_long CHANGE pc6 pc6 VARCHAR(6) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pc_lat_long CHANGE pc6 pc6 CHAR(6) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX pc ON pc_lat_long (pc6)');
    }
}
