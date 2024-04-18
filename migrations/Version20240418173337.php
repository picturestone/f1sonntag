<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240418173337 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE driver DROP FOREIGN KEY FK_11667CD9296CD8AE');
        $this->addSql('ALTER TABLE driver ADD CONSTRAINT FK_11667CD9296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE penalty_points_award DROP FOREIGN KEY FK_FD3A7EE46E59D40D');
        $this->addSql('ALTER TABLE penalty_points_award DROP FOREIGN KEY FK_FD3A7EE4A76ED395');
        $this->addSql('ALTER TABLE penalty_points_award ADD CONSTRAINT FK_FD3A7EE46E59D40D FOREIGN KEY (race_id) REFERENCES race (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE penalty_points_award ADD CONSTRAINT FK_FD3A7EE4A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE race DROP FOREIGN KEY FK_DA6FBBAF4EC001D1');
        $this->addSql('ALTER TABLE race CHANGE season_id season_id INT NOT NULL');
        $this->addSql('ALTER TABLE race ADD CONSTRAINT FK_DA6FBBAF4EC001D1 FOREIGN KEY (season_id) REFERENCES season (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE race_result DROP FOREIGN KEY FK_793CDFC06E59D40D');
        $this->addSql('ALTER TABLE race_result DROP FOREIGN KEY FK_793CDFC0C3423909');
        $this->addSql('ALTER TABLE race_result ADD CONSTRAINT FK_793CDFC06E59D40D FOREIGN KEY (race_id) REFERENCES race (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE race_result ADD CONSTRAINT FK_793CDFC0C3423909 FOREIGN KEY (driver_id) REFERENCES driver (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE race_result_bet DROP FOREIGN KEY FK_1C29D90D6E59D40D');
        $this->addSql('ALTER TABLE race_result_bet DROP FOREIGN KEY FK_1C29D90DA76ED395');
        $this->addSql('ALTER TABLE race_result_bet DROP FOREIGN KEY FK_1C29D90DC3423909');
        $this->addSql('ALTER TABLE race_result_bet CHANGE user_id user_id INT NOT NULL, CHANGE driver_id driver_id INT NOT NULL, CHANGE race_id race_id INT NOT NULL');
        $this->addSql('ALTER TABLE race_result_bet ADD CONSTRAINT FK_1C29D90D6E59D40D FOREIGN KEY (race_id) REFERENCES race (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE race_result_bet ADD CONSTRAINT FK_1C29D90DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE race_result_bet ADD CONSTRAINT FK_1C29D90DC3423909 FOREIGN KEY (driver_id) REFERENCES driver (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE season DROP FOREIGN KEY FK_F0E45BA923CDCFB3');
        $this->addSql('ALTER TABLE season ADD CONSTRAINT FK_F0E45BA923CDCFB3 FOREIGN KEY (world_champion_id) REFERENCES driver (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE world_champion_bet DROP FOREIGN KEY FK_C26FABF14EC001D1');
        $this->addSql('ALTER TABLE world_champion_bet DROP FOREIGN KEY FK_C26FABF1A76ED395');
        $this->addSql('ALTER TABLE world_champion_bet DROP FOREIGN KEY FK_C26FABF1C3423909');
        $this->addSql('ALTER TABLE world_champion_bet CHANGE driver_id driver_id INT NOT NULL, CHANGE user_id user_id INT NOT NULL, CHANGE season_id season_id INT NOT NULL');
        $this->addSql('ALTER TABLE world_champion_bet ADD CONSTRAINT FK_C26FABF14EC001D1 FOREIGN KEY (season_id) REFERENCES season (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE world_champion_bet ADD CONSTRAINT FK_C26FABF1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE world_champion_bet ADD CONSTRAINT FK_C26FABF1C3423909 FOREIGN KEY (driver_id) REFERENCES driver (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE race DROP FOREIGN KEY FK_DA6FBBAF4EC001D1');
        $this->addSql('ALTER TABLE race CHANGE season_id season_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE race ADD CONSTRAINT FK_DA6FBBAF4EC001D1 FOREIGN KEY (season_id) REFERENCES season (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE race_result DROP FOREIGN KEY FK_793CDFC0C3423909');
        $this->addSql('ALTER TABLE race_result DROP FOREIGN KEY FK_793CDFC06E59D40D');
        $this->addSql('ALTER TABLE race_result ADD CONSTRAINT FK_793CDFC0C3423909 FOREIGN KEY (driver_id) REFERENCES driver (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE race_result ADD CONSTRAINT FK_793CDFC06E59D40D FOREIGN KEY (race_id) REFERENCES race (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE penalty_points_award DROP FOREIGN KEY FK_FD3A7EE4A76ED395');
        $this->addSql('ALTER TABLE penalty_points_award DROP FOREIGN KEY FK_FD3A7EE46E59D40D');
        $this->addSql('ALTER TABLE penalty_points_award ADD CONSTRAINT FK_FD3A7EE4A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE penalty_points_award ADD CONSTRAINT FK_FD3A7EE46E59D40D FOREIGN KEY (race_id) REFERENCES race (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE race_result_bet DROP FOREIGN KEY FK_1C29D90DA76ED395');
        $this->addSql('ALTER TABLE race_result_bet DROP FOREIGN KEY FK_1C29D90DC3423909');
        $this->addSql('ALTER TABLE race_result_bet DROP FOREIGN KEY FK_1C29D90D6E59D40D');
        $this->addSql('ALTER TABLE race_result_bet CHANGE user_id user_id INT DEFAULT NULL, CHANGE driver_id driver_id INT DEFAULT NULL, CHANGE race_id race_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE race_result_bet ADD CONSTRAINT FK_1C29D90DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE race_result_bet ADD CONSTRAINT FK_1C29D90DC3423909 FOREIGN KEY (driver_id) REFERENCES driver (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE race_result_bet ADD CONSTRAINT FK_1C29D90D6E59D40D FOREIGN KEY (race_id) REFERENCES race (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE season DROP FOREIGN KEY FK_F0E45BA923CDCFB3');
        $this->addSql('ALTER TABLE season ADD CONSTRAINT FK_F0E45BA923CDCFB3 FOREIGN KEY (world_champion_id) REFERENCES driver (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE world_champion_bet DROP FOREIGN KEY FK_C26FABF1C3423909');
        $this->addSql('ALTER TABLE world_champion_bet DROP FOREIGN KEY FK_C26FABF1A76ED395');
        $this->addSql('ALTER TABLE world_champion_bet DROP FOREIGN KEY FK_C26FABF14EC001D1');
        $this->addSql('ALTER TABLE world_champion_bet CHANGE driver_id driver_id INT DEFAULT NULL, CHANGE user_id user_id INT DEFAULT NULL, CHANGE season_id season_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE world_champion_bet ADD CONSTRAINT FK_C26FABF1C3423909 FOREIGN KEY (driver_id) REFERENCES driver (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE world_champion_bet ADD CONSTRAINT FK_C26FABF1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE world_champion_bet ADD CONSTRAINT FK_C26FABF14EC001D1 FOREIGN KEY (season_id) REFERENCES season (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE driver DROP FOREIGN KEY FK_11667CD9296CD8AE');
        $this->addSql('ALTER TABLE driver ADD CONSTRAINT FK_11667CD9296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
