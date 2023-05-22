<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230522144501 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE pc_lat_long');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE pc_lat_long (id INT AUTO_INCREMENT NOT NULL, pc6 CHAR(6) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_general_ci`, lat NUMERIC(10, 8) DEFAULT NULL, lng NUMERIC(10, 8) DEFAULT NULL, UNIQUE INDEX pc (pc6), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
    }
}
