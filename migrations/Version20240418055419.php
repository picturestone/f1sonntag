<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240418055419 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE world_champion MODIFY id INT NOT NULL');
        $this->addSql('DROP INDEX `primary` ON world_champion');
        $this->addSql('ALTER TABLE world_champion DROP id, CHANGE driver_id driver_id INT NOT NULL, CHANGE season_id season_id INT NOT NULL');
        $this->addSql('ALTER TABLE world_champion ADD PRIMARY KEY (driver_id, season_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE world_champion ADD id INT AUTO_INCREMENT NOT NULL, CHANGE driver_id driver_id INT DEFAULT NULL, CHANGE season_id season_id INT DEFAULT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
    }
}
