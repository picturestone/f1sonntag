<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240413222233 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE driver ADD team_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE driver ADD CONSTRAINT FK_11667CD9296CD8AE FOREIGN KEY (team_id) REFERENCES team (id)');
        $this->addSql('CREATE INDEX IDX_11667CD9296CD8AE ON driver (team_id)');
        $this->addSql('ALTER TABLE position_bet ADD user_id INT DEFAULT NULL, ADD driver_id INT DEFAULT NULL, ADD race_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE position_bet ADD CONSTRAINT FK_F46E483A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE position_bet ADD CONSTRAINT FK_F46E483C3423909 FOREIGN KEY (driver_id) REFERENCES driver (id)');
        $this->addSql('ALTER TABLE position_bet ADD CONSTRAINT FK_F46E4836E59D40D FOREIGN KEY (race_id) REFERENCES race (id)');
        $this->addSql('CREATE INDEX IDX_F46E483A76ED395 ON position_bet (user_id)');
        $this->addSql('CREATE INDEX IDX_F46E483C3423909 ON position_bet (driver_id)');
        $this->addSql('CREATE INDEX IDX_F46E4836E59D40D ON position_bet (race_id)');
        $this->addSql('ALTER TABLE punishment_points ADD user_id INT DEFAULT NULL, ADD race_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE punishment_points ADD CONSTRAINT FK_FF121101A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE punishment_points ADD CONSTRAINT FK_FF1211016E59D40D FOREIGN KEY (race_id) REFERENCES race (id)');
        $this->addSql('CREATE INDEX IDX_FF121101A76ED395 ON punishment_points (user_id)');
        $this->addSql('CREATE INDEX IDX_FF1211016E59D40D ON punishment_points (race_id)');
        $this->addSql('ALTER TABLE race ADD season_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE race ADD CONSTRAINT FK_DA6FBBAF4EC001D1 FOREIGN KEY (season_id) REFERENCES season (id)');
        $this->addSql('CREATE INDEX IDX_DA6FBBAF4EC001D1 ON race (season_id)');
        $this->addSql('ALTER TABLE race_result ADD driver_id INT DEFAULT NULL, ADD race_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE race_result ADD CONSTRAINT FK_793CDFC0C3423909 FOREIGN KEY (driver_id) REFERENCES driver (id)');
        $this->addSql('ALTER TABLE race_result ADD CONSTRAINT FK_793CDFC06E59D40D FOREIGN KEY (race_id) REFERENCES race (id)');
        $this->addSql('CREATE INDEX IDX_793CDFC0C3423909 ON race_result (driver_id)');
        $this->addSql('CREATE INDEX IDX_793CDFC06E59D40D ON race_result (race_id)');
        $this->addSql('ALTER TABLE world_champion_bet ADD driver_id INT DEFAULT NULL, ADD user_id INT DEFAULT NULL, ADD season_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE world_champion_bet ADD CONSTRAINT FK_C26FABF1C3423909 FOREIGN KEY (driver_id) REFERENCES driver (id)');
        $this->addSql('ALTER TABLE world_champion_bet ADD CONSTRAINT FK_C26FABF1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE world_champion_bet ADD CONSTRAINT FK_C26FABF14EC001D1 FOREIGN KEY (season_id) REFERENCES season (id)');
        $this->addSql('CREATE INDEX IDX_C26FABF1C3423909 ON world_champion_bet (driver_id)');
        $this->addSql('CREATE INDEX IDX_C26FABF1A76ED395 ON world_champion_bet (user_id)');
        $this->addSql('CREATE INDEX IDX_C26FABF14EC001D1 ON world_champion_bet (season_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE world_champion_bet DROP FOREIGN KEY FK_C26FABF1C3423909');
        $this->addSql('ALTER TABLE world_champion_bet DROP FOREIGN KEY FK_C26FABF1A76ED395');
        $this->addSql('ALTER TABLE world_champion_bet DROP FOREIGN KEY FK_C26FABF14EC001D1');
        $this->addSql('DROP INDEX IDX_C26FABF1C3423909 ON world_champion_bet');
        $this->addSql('DROP INDEX IDX_C26FABF1A76ED395 ON world_champion_bet');
        $this->addSql('DROP INDEX IDX_C26FABF14EC001D1 ON world_champion_bet');
        $this->addSql('ALTER TABLE world_champion_bet DROP driver_id, DROP user_id, DROP season_id');
        $this->addSql('ALTER TABLE punishment_points DROP FOREIGN KEY FK_FF121101A76ED395');
        $this->addSql('ALTER TABLE punishment_points DROP FOREIGN KEY FK_FF1211016E59D40D');
        $this->addSql('DROP INDEX IDX_FF121101A76ED395 ON punishment_points');
        $this->addSql('DROP INDEX IDX_FF1211016E59D40D ON punishment_points');
        $this->addSql('ALTER TABLE punishment_points DROP user_id, DROP race_id');
        $this->addSql('ALTER TABLE race DROP FOREIGN KEY FK_DA6FBBAF4EC001D1');
        $this->addSql('DROP INDEX IDX_DA6FBBAF4EC001D1 ON race');
        $this->addSql('ALTER TABLE race DROP season_id');
        $this->addSql('ALTER TABLE position_bet DROP FOREIGN KEY FK_F46E483A76ED395');
        $this->addSql('ALTER TABLE position_bet DROP FOREIGN KEY FK_F46E483C3423909');
        $this->addSql('ALTER TABLE position_bet DROP FOREIGN KEY FK_F46E4836E59D40D');
        $this->addSql('DROP INDEX IDX_F46E483A76ED395 ON position_bet');
        $this->addSql('DROP INDEX IDX_F46E483C3423909 ON position_bet');
        $this->addSql('DROP INDEX IDX_F46E4836E59D40D ON position_bet');
        $this->addSql('ALTER TABLE position_bet DROP user_id, DROP driver_id, DROP race_id');
        $this->addSql('ALTER TABLE race_result DROP FOREIGN KEY FK_793CDFC0C3423909');
        $this->addSql('ALTER TABLE race_result DROP FOREIGN KEY FK_793CDFC06E59D40D');
        $this->addSql('DROP INDEX IDX_793CDFC0C3423909 ON race_result');
        $this->addSql('DROP INDEX IDX_793CDFC06E59D40D ON race_result');
        $this->addSql('ALTER TABLE race_result DROP driver_id, DROP race_id');
        $this->addSql('ALTER TABLE driver DROP FOREIGN KEY FK_11667CD9296CD8AE');
        $this->addSql('DROP INDEX IDX_11667CD9296CD8AE ON driver');
        $this->addSql('ALTER TABLE driver DROP team_id');
    }
}
