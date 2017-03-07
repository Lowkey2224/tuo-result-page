<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170302183108 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE `battle_ground_effect` CHANGE `updated_at` `updated_at` DATETIME NULL DEFAULT NULL;');
        $this->addSql('ALTER TABLE `battle_ground_effect` CHANGE `updated_at` `updated_at` DATETIME NULL DEFAULT NULL;');
        $this->addSql('ALTER TABLE `card` CHANGE `updated_at` `updated_at` DATETIME NULL DEFAULT NULL;');
        $this->addSql('ALTER TABLE `card` CHANGE `updated_at` `updated_at` DATETIME NULL DEFAULT NULL;');
        $this->addSql('ALTER TABLE `card_file` CHANGE `updated_at` `updated_at` DATETIME NULL DEFAULT NULL;');
        $this->addSql('ALTER TABLE `card_file` CHANGE `updated_at` `updated_at` DATETIME NULL DEFAULT NULL;');
        $this->addSql('ALTER TABLE `deck_entry` CHANGE `updated_at` `updated_at` DATETIME NULL DEFAULT NULL;');
        $this->addSql('ALTER TABLE `deck_entry` CHANGE `updated_at` `updated_at` DATETIME NULL DEFAULT NULL;');
        $this->addSql('ALTER TABLE `mission` CHANGE `updated_at` `updated_at` DATETIME NULL DEFAULT NULL;');
        $this->addSql('ALTER TABLE `mission` CHANGE `updated_at` `updated_at` DATETIME NULL DEFAULT NULL;');
        $this->addSql('ALTER TABLE `owned_card` CHANGE `updated_at` `updated_at` DATETIME NULL DEFAULT NULL;');
        $this->addSql('ALTER TABLE `owned_card` CHANGE `updated_at` `updated_at` DATETIME NULL DEFAULT NULL;');
        $this->addSql('ALTER TABLE `result` CHANGE `updated_at` `updated_at` DATETIME NULL DEFAULT NULL;');
        $this->addSql('ALTER TABLE `result` CHANGE `updated_at` `updated_at` DATETIME NULL DEFAULT NULL;');
        $this->addSql('ALTER TABLE `result_file` CHANGE `updated_at` `updated_at` DATETIME NULL DEFAULT NULL;');
        $this->addSql('ALTER TABLE `result_file` CHANGE `updated_at` `updated_at` DATETIME NULL DEFAULT NULL;');
        $this->addSql('ALTER TABLE `player` CHANGE `updated_at` `updated_at` DATETIME NULL DEFAULT NULL;');
        $this->addSql('ALTER TABLE `player` CHANGE `created_at` `created_at` DATETIME NULL DEFAULT NULL;');
        $this->addSql('ALTER TABLE player ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE player ADD CONSTRAINT FK_98197A65A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_98197A65A76ED395 ON player (user_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE player DROP FOREIGN KEY FK_98197A65A76ED395');
        $this->addSql('DROP INDEX IDX_98197A65A76ED395 ON player');
        $this->addSql('ALTER TABLE player DROP user_id');
    }
}
