<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240417210504 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE punishment_points MODIFY id INT NOT NULL');
        $this->addSql('DROP INDEX `primary` ON punishment_points');
        $this->addSql('ALTER TABLE punishment_points DROP id, CHANGE user_id user_id INT NOT NULL, CHANGE race_id race_id INT NOT NULL');
        $this->addSql('ALTER TABLE punishment_points ADD PRIMARY KEY (user_id, race_id)');
        $this->addSql('ALTER TABLE race_result MODIFY id INT NOT NULL');
        $this->addSql('DROP INDEX `primary` ON race_result');
        $this->addSql('ALTER TABLE race_result DROP id, CHANGE driver_id driver_id INT NOT NULL, CHANGE race_id race_id INT NOT NULL');
        $this->addSql('ALTER TABLE race_result ADD PRIMARY KEY (driver_id, race_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE punishment_points ADD id INT AUTO_INCREMENT NOT NULL, CHANGE user_id user_id INT DEFAULT NULL, CHANGE race_id race_id INT DEFAULT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE race_result ADD id INT AUTO_INCREMENT NOT NULL, CHANGE driver_id driver_id INT DEFAULT NULL, CHANGE race_id race_id INT DEFAULT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
    }
}
