<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240418054519 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE penalty_points_award (user_id INT NOT NULL, race_id INT NOT NULL, penalty_points INT NOT NULL, INDEX IDX_FD3A7EE4A76ED395 (user_id), INDEX IDX_FD3A7EE46E59D40D (race_id), PRIMARY KEY(user_id, race_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE penalty_points_award ADD CONSTRAINT FK_FD3A7EE4A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE penalty_points_award ADD CONSTRAINT FK_FD3A7EE46E59D40D FOREIGN KEY (race_id) REFERENCES race (id)');
        $this->addSql('ALTER TABLE punishment_points DROP FOREIGN KEY FK_FF1211016E59D40D');
        $this->addSql('ALTER TABLE punishment_points DROP FOREIGN KEY FK_FF121101A76ED395');
        $this->addSql('DROP TABLE punishment_points');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE punishment_points (user_id INT NOT NULL, race_id INT NOT NULL, penalty_points INT NOT NULL, INDEX IDX_FF121101A76ED395 (user_id), INDEX IDX_FF1211016E59D40D (race_id), PRIMARY KEY(user_id, race_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE punishment_points ADD CONSTRAINT FK_FF1211016E59D40D FOREIGN KEY (race_id) REFERENCES race (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE punishment_points ADD CONSTRAINT FK_FF121101A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE penalty_points_award DROP FOREIGN KEY FK_FD3A7EE4A76ED395');
        $this->addSql('ALTER TABLE penalty_points_award DROP FOREIGN KEY FK_FD3A7EE46E59D40D');
        $this->addSql('DROP TABLE penalty_points_award');
    }
}
