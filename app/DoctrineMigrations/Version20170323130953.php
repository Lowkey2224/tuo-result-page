<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use LokiTuoResultBundle\Entity\Guild;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170323130953 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql',
            'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('Update result set created_at = \'2015-12-12 12:00:00\' where created_at < \'2015-09-01 00:00:00\'');
        $this->addSql('Update result set updated_at = \'2015-12-12 12:00:00\' where updated_at < \'2015-09-01 00:00:00\'');
        $this->addSql('Update player set created_at = updated_at where created_at is null');
        $this->addSql('CREATE TABLE guild (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE player ADD guild_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE player ADD CONSTRAINT FK_98197A655F2131EF FOREIGN KEY (guild_id) REFERENCES guild (id)');
        $this->addSql('CREATE INDEX IDX_98197A655F2131EF ON player (guild_id)');
        $this->addSql('ALTER TABLE result ADD guild_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE result ADD CONSTRAINT FK_136AC1135F2131EF FOREIGN KEY (guild_id) REFERENCES guild (id)');
        $this->addSql('CREATE INDEX IDX_136AC1135F2131EF ON result (guild_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql',
            'Migration can only be executed safely on \'mysql\'.');

        //Rollback Player
        $this->addSql('ALTER TABLE player DROP FOREIGN KEY FK_98197A655F2131EF');
        $this->addSql('DROP INDEX IDX_98197A655F2131EF ON player');
        $this->addSql('ALTER TABLE player DROP guild_id');
        // ROllback Result
        $this->addSql('ALTER TABLE result DROP FOREIGN KEY FK_136AC1135F2131EF');
        $this->addSql('DROP INDEX IDX_136AC1135F2131EF ON result');
        $this->addSql('ALTER TABLE result DROP guild_id');
        // Drop Table Guild
        $this->addSql('DROP TABLE guild');
    }
}
