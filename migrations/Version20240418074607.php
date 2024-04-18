<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240418074607 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE season ADD world_champion_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE season ADD CONSTRAINT FK_F0E45BA923CDCFB3 FOREIGN KEY (world_champion_id) REFERENCES driver (id)');
        $this->addSql('CREATE INDEX IDX_F0E45BA923CDCFB3 ON season (world_champion_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE season DROP FOREIGN KEY FK_F0E45BA923CDCFB3');
        $this->addSql('DROP INDEX IDX_F0E45BA923CDCFB3 ON season');
        $this->addSql('ALTER TABLE season DROP world_champion_id');
    }
}
