<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171108140007 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE ext_translations (
          id INT AUTO_INCREMENT NOT NULL, 
          locale VARCHAR(8) NOT NULL, 
          object_class VARCHAR(255) NOT NULL, 
          field VARCHAR(32) NOT NULL, 
          foreign_key VARCHAR(64) NOT NULL, 
          content LONGTEXT DEFAULT NULL, 
          INDEX translations_lookup_idx (
            locale, object_class, foreign_key
          ), 
          UNIQUE INDEX lookup_unique_idx (
            locale, object_class, field, foreign_key
          ), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE message (
          id INT AUTO_INCREMENT NOT NULL, 
          player_id INT DEFAULT NULL, 
          status INT NOT NULL COMMENT \'1 => Ungelesen, 2 => Gelesen\', 
          message VARCHAR(255) NOT NULL, 
          created_at DATETIME DEFAULT NULL, 
          updated_at DATETIME DEFAULT NULL, 
          INDEX IDX_B6BD307F99E6F5DF (player_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          message 
        ADD 
          CONSTRAINT FK_B6BD307F99E6F5DF FOREIGN KEY (player_id) REFERENCES player (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE ext_translations');
        $this->addSql('DROP TABLE message');
    }
}
