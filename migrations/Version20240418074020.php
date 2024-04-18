<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240418074020 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE world_champion DROP FOREIGN KEY FK_B0A3FFCD4EC001D1');
        $this->addSql('ALTER TABLE world_champion DROP FOREIGN KEY FK_B0A3FFCDC3423909');
        $this->addSql('DROP TABLE world_champion');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE world_champion (driver_id INT NOT NULL, season_id INT NOT NULL, INDEX IDX_B0A3FFCD4EC001D1 (season_id), INDEX IDX_B0A3FFCDC3423909 (driver_id), PRIMARY KEY(driver_id, season_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE world_champion ADD CONSTRAINT FK_B0A3FFCD4EC001D1 FOREIGN KEY (season_id) REFERENCES season (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE world_champion ADD CONSTRAINT FK_B0A3FFCDC3423909 FOREIGN KEY (driver_id) REFERENCES driver (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
