<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171026092650 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql("UPDATE `player` set `updated_at` = '2000-11-18 14:09:49' WHERE `updated_at` IS NULL");
        //Cleanup Old Players
        $this->addSql("DELETE  FROM `player` WHERE `guild_id` = 5 or `active` = 0");


        $this->addSql('CREATE TABLE queue_item (
          id INT AUTO_INCREMENT NOT NULL, 
          user_id INT NOT NULL, 
          name VARCHAR(255) NOT NULL, 
          status VARCHAR(255) NOT NULL, 
          message VARCHAR(255) DEFAULT NULL, 
          created_at DATETIME DEFAULT NULL, 
          updated_at DATETIME DEFAULT NULL, 
          UNIQUE INDEX UNIQ_BA4B6DE85E237E06 (name), 
          INDEX IDX_BA4B6DE8A76ED395 (user_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          queue_item 
        ADD 
          CONSTRAINT FK_BA4B6DE8A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE queue_item');
    }
}
