<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170825162105 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

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
          created_at DATETIME DEFAULT NULL, 
          updated_at DATETIME DEFAULT NULL, 
          INDEX IDX_A2DE3DD54ACC9A20 (card_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          card_level 
        ADD 
          CONSTRAINT FK_A2DE3DD54ACC9A20 FOREIGN KEY (card_id) REFERENCES card (id)');
        $this->addSql('ALTER TABLE card DROP Attack, DROP Defense, DROP Delay, DROP Picture, DROP skills, DROP tuo_id');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

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
          Picture VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, 
        ADD 
          skills LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:array)\', 
        ADD 
          tuo_id INT NOT NULL');
    }
}
