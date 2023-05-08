<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230503105939 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer ADD first_name VARCHAR(255) NOT NULL, ADD last_name VARCHAR(255) NOT NULL, ADD email VARCHAR(255) NOT NULL, ADD phonenumber VARCHAR(255) NOT NULL, ADD postcode VARCHAR(6) NOT NULL, ADD city VARCHAR(255) NOT NULL, ADD street VARCHAR(255) NOT NULL, ADD house_number VARCHAR(100) NOT NULL, ADD bank_acount VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer DROP first_name, DROP last_name, DROP email, DROP phonenumber, DROP postcode, DROP city, DROP street, DROP house_number, DROP bank_acount');
    }
}
