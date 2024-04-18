<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240418051025 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE race_result_bet (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, driver_id INT DEFAULT NULL, race_id INT DEFAULT NULL, position INT NOT NULL, INDEX IDX_1C29D90DA76ED395 (user_id), INDEX IDX_1C29D90DC3423909 (driver_id), INDEX IDX_1C29D90D6E59D40D (race_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE race_result_bet ADD CONSTRAINT FK_1C29D90DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE race_result_bet ADD CONSTRAINT FK_1C29D90DC3423909 FOREIGN KEY (driver_id) REFERENCES driver (id)');
        $this->addSql('ALTER TABLE race_result_bet ADD CONSTRAINT FK_1C29D90D6E59D40D FOREIGN KEY (race_id) REFERENCES race (id)');
        $this->addSql('ALTER TABLE position_bet DROP FOREIGN KEY FK_F46E4836E59D40D');
        $this->addSql('ALTER TABLE position_bet DROP FOREIGN KEY FK_F46E483A76ED395');
        $this->addSql('ALTER TABLE position_bet DROP FOREIGN KEY FK_F46E483C3423909');
        $this->addSql('DROP TABLE position_bet');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE position_bet (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, driver_id INT DEFAULT NULL, race_id INT DEFAULT NULL, position INT NOT NULL, INDEX IDX_F46E4836E59D40D (race_id), INDEX IDX_F46E483C3423909 (driver_id), INDEX IDX_F46E483A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE position_bet ADD CONSTRAINT FK_F46E4836E59D40D FOREIGN KEY (race_id) REFERENCES race (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE position_bet ADD CONSTRAINT FK_F46E483A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE position_bet ADD CONSTRAINT FK_F46E483C3423909 FOREIGN KEY (driver_id) REFERENCES driver (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE race_result_bet DROP FOREIGN KEY FK_1C29D90DA76ED395');
        $this->addSql('ALTER TABLE race_result_bet DROP FOREIGN KEY FK_1C29D90DC3423909');
        $this->addSql('ALTER TABLE race_result_bet DROP FOREIGN KEY FK_1C29D90D6E59D40D');
        $this->addSql('DROP TABLE race_result_bet');
    }
}
