<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251012124808 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE inspections (id INT AUTO_INCREMENT NOT NULL, created_by_user_id INT NOT NULL, start_datetime DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', end_datetime DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', vehicle_make VARCHAR(64) NOT NULL, vehicle_model VARCHAR(64) NOT NULL, license_plate VARCHAR(20) NOT NULL, client_name VARCHAR(64) NOT NULL, phone_number VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX idx_start_datetime (start_datetime), INDEX idx_end_datetime (end_datetime), INDEX idx_created_by_user_id (created_by_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE inspections ADD CONSTRAINT FK_862549907D182D95 FOREIGN KEY (created_by_user_id) REFERENCES users (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inspections DROP FOREIGN KEY FK_862549907D182D95');
        $this->addSql('DROP TABLE inspections');
    }
}
