<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171016112446 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE kongregate_credentials (
          id INT AUTO_INCREMENT NOT NULL, 
          kong_password VARCHAR(255) DEFAULT NULL, 
          tu_user_id INT DEFAULT NULL, 
          syn_code VARCHAR(255) DEFAULT NULL, 
          kong_user_name VARCHAR(255) DEFAULT NULL, 
          kong_id INT DEFAULT NULL, 
          kong_token VARCHAR(255) DEFAULT NULL, 
          created_at DATETIME DEFAULT NULL, 
          updated_at DATETIME DEFAULT NULL, 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          card_level CHANGE created_at created_at DATETIME DEFAULT NULL, 
          CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          deck_entry CHANGE created_at created_at DATETIME DEFAULT NULL, 
          CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          player 
        ADD 
          kong_credentials_id INT DEFAULT NULL, 
        DROP 
          kong_password, 
        DROP 
          tu_user_id, 
        DROP 
          syn_code, 
        DROP 
          kong_user_name, 
        DROP 
          kong_id, 
        DROP 
          kong_token');
        $this->addSql('ALTER TABLE 
          player 
        ADD 
          CONSTRAINT FK_98197A65B6FC92A0 FOREIGN KEY (kong_credentials_id) REFERENCES kongregate_credentials (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_98197A65B6FC92A0 ON player (kong_credentials_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE player DROP FOREIGN KEY FK_98197A65B6FC92A0');
        $this->addSql('DROP TABLE kongregate_credentials');
        $this->addSql('ALTER TABLE 
          card_level CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP, 
          CHANGE updated_at updated_at DATETIME DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE 
          deck_entry CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP, 
          CHANGE updated_at updated_at DATETIME DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('DROP INDEX UNIQ_98197A65B6FC92A0 ON player');
        $this->addSql('ALTER TABLE 
          player 
        ADD 
          kong_password VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, 
        ADD 
          syn_code VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, 
        ADD 
          kong_user_name VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, 
        ADD 
          kong_id INT DEFAULT NULL, 
        ADD 
          kong_token VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, 
          CHANGE kong_credentials_id tu_user_id INT DEFAULT NULL');
    }
}
