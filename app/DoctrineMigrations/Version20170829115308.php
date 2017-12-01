<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170829115308 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('Update deck_entry set created_at = \'2015-12-12 12:00:00\' where created_at < \'2015-09-01 00:00:00\'');
        $this->addSql('Update deck_entry set updated_at = \'2015-12-12 12:00:00\' where updated_at < \'2015-09-01 00:00:00\'');
        $this->addSql('ALTER TABLE `deck_entry` CHANGE `created_at` `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE `deck_entry` CHANGE `updated_at` `updated_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('CREATE TABLE card_level (
          id INT AUTO_INCREMENT NOT NULL, 
          card_id INT DEFAULT NULL, 
          Attack INT DEFAULT NULL, 
          Defense INT DEFAULT NULL, 
          Delay INT DEFAULT NULL, 
          Picture VARCHAR(255) DEFAULT NULL, 
          skills LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', 
          level INT NOT NULL, 
          tuo_id INT NOT NULL, 
          created_at DATETIME DEFAULT CURRENT_TIMESTAMP, 
          updated_at DATETIME DEFAULT CURRENT_TIMESTAMP, 
          INDEX IDX_A2DE3DD54ACC9A20 (card_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          card_level 
        ADD 
          CONSTRAINT FK_A2DE3DD54ACC9A20 FOREIGN KEY (card_id) REFERENCES card (id)');
        $this->addSql('ALTER TABLE card DROP Attack, DROP Defense, DROP Delay, DROP Picture, DROP skills, DROP tuo_id');
        $this->addSql('ALTER TABLE deck_entry DROP FOREIGN KEY FK_4FAC36374ACC9A20');
        $this->addSql('DROP INDEX IDX_EA7C679F4ACC9A20 ON deck_entry');
        $this->addSql('ALTER TABLE deck_entry ADD card_level_id INT DEFAULT NULL, DROP card_id, DROP level');
        $this->addSql('ALTER TABLE 
          deck_entry 
        ADD 
          CONSTRAINT FK_EA7C679FE6808524 FOREIGN KEY (card_level_id) REFERENCES card_level (id)');
        $this->addSql('CREATE INDEX IDX_EA7C679FE6808524 ON deck_entry (card_level_id)');
        $this->addSql('ALTER TABLE owned_card DROP FOREIGN KEY FK_553D1AC54ACC9A20');
        $this->addSql('DROP INDEX IDX_553D1AC54ACC9A20 ON owned_card');
        $this->addSql('ALTER TABLE owned_card ADD card_level_id INT DEFAULT NULL, DROP card_id, DROP level');
        $this->addSql('ALTER TABLE 
          owned_card 
        ADD 
          CONSTRAINT FK_553D1AC5E6808524 FOREIGN KEY (card_level_id) REFERENCES card_level (id)');
        $this->addSql('CREATE INDEX IDX_553D1AC5E6808524 ON owned_card (card_level_id)');
        $this->addSql('ALTER TABLE 
          player 
        ADD 
          kong_password VARCHAR(255) DEFAULT NULL, 
        ADD 
          tu_user_id INT DEFAULT NULL, 
        ADD 
          syn_code VARCHAR(255) DEFAULT NULL, 
        ADD 
          kong_user_name VARCHAR(255) DEFAULT NULL, 
        ADD 
          kong_id INT DEFAULT NULL, 
        ADD 
          kong_token VARCHAR(255) DEFAULT NULL');

        $this->addSql("TRUNCATE TABLE deck_entry;");
        $this->addSql("Delete FROM result where 1;");
        $this->addSql("Delete FROM owned_card where 1;");
        $this->addSql("DELETE FROM card_level WHERE 1;");
        $this->addSql("Delete FROM card where 1;");
        $this->addSql("Delete FROM card_file where 1;");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE deck_entry DROP FOREIGN KEY FK_EA7C679FE6808524');
        $this->addSql('ALTER TABLE owned_card DROP FOREIGN KEY FK_553D1AC5E6808524');
        $this->addSql('DROP TABLE card_level');
        $this->addSql('ALTER TABLE 
          card 
        ADD 
          Attack INT NOT NULL, 
        ADD 
          Defense INT NOT NULL, 
        ADD 
          Delay INT NOT NULL,  
        ADD 
          Delay INT DEFAULT NULL, 
        ADD 
          Picture VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, 
        ADD 
          skills LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:array)\'');
        $this->addSql('DROP INDEX IDX_EA7C679FE6808524 ON deck_entry');
        $this->addSql('ALTER TABLE 
          deck_entry 
        ADD 
          level INT DEFAULT NULL, 
          CHANGE card_level_id card_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          deck_entry 
        ADD 
          CONSTRAINT FK_4FAC36374ACC9A20 FOREIGN KEY (card_id) REFERENCES card (id)');
        $this->addSql('CREATE INDEX IDX_EA7C679F4ACC9A20 ON deck_entry (card_id)');
        $this->addSql('DROP INDEX IDX_553D1AC5E6808524 ON owned_card');
        $this->addSql('ALTER TABLE 
          owned_card 
        ADD 
          level INT DEFAULT NULL, 
          CHANGE card_level_id card_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          owned_card 
        ADD 
          CONSTRAINT FK_553D1AC54ACC9A20 FOREIGN KEY (card_id) REFERENCES card (id)');
        $this->addSql('CREATE INDEX IDX_553D1AC54ACC9A20 ON owned_card (card_id)');
        $this->addSql('ALTER TABLE 
          player 
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
    }
}
