<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241219113833 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD lastname VARCHAR(255) NOT NULL, ADD firstname VARCHAR(255) NOT NULL, ADD image VARCHAR(255) NOT NULL, ADD longitude NUMERIC(20, 16) DEFAULT NULL, ADD latitude NUMERIC(20, 16) DEFAULT NULL, ADD birthdate DATE NOT NULL, ADD score NUMERIC(10, 2) NOT NULL, ADD interests JSON NOT NULL COMMENT \'(DC2Type:json)\', ADD bio LONGTEXT NOT NULL, ADD gender VARCHAR(255) NOT NULL, ADD created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP lastname, DROP firstname, DROP image, DROP longitude, DROP latitude, DROP birthdate, DROP score, DROP interests, DROP bio, DROP gender, DROP created_at, DROP updated_at');
    }
}
