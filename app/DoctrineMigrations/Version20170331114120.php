<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170331114120 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX UNIQ_9067F23CFE11D138 ON mission');
        $this->addSql('ALTER TABLE mission ADD bge_id INT DEFAULT NULL, ADD structures VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE mission ADD CONSTRAINT FK_9067F23C4A14BFBC FOREIGN KEY (bge_id) REFERENCES battle_ground_effect (id)');
        $this->addSql('CREATE INDEX IDX_9067F23C4A14BFBC ON mission (bge_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mission DROP FOREIGN KEY FK_9067F23C4A14BFBC');
        $this->addSql('DROP INDEX IDX_9067F23C4A14BFBC ON mission');
        $this->addSql('ALTER TABLE mission DROP bge_id, DROP structures');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9067F23CFE11D138 ON mission (Name)');
    }
}
