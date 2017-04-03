<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170331140801 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mission ADD uuid VARCHAR(255) NOT NULL');
        $this->addSql('UPDATE mission SET uuid =  CONCAT_WS("|","mission",Name, "bge", IF(bge_id is null, "null", bge_id), "structures", IF(structures is null, "null", structures))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9067F23CD17F50A6 ON mission (uuid)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX UNIQ_9067F23CD17F50A6 ON mission');
        $this->addSql('ALTER TABLE mission DROP uuid');
    }
}
