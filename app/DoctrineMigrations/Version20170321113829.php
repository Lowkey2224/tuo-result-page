<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170321113829 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('UPDATE `mission` SET `updated_at`= \'2015-09-01 00:00:00\' WHERE `updated_at` is null ');
        $this->addSql('UPDATE `mission` SET `created_at`= \'2015-09-01 00:00:00\' WHERE `created_at` is null ');
        $this->addSql('UPDATE `result` SET `updated_at`= \'2015-09-01 00:00:00\' WHERE `updated_at` is null');
        $this->addSql('UPDATE `result` SET `created_at`= \'2015-09-01 00:00:00\' WHERE `created_at` is null');

        $this->addSql('UPDATE `mission` SET `updated_at`=(SELECT max(updated_at) FROM `result` where mission_id = `mission`.`id`) WHERE 1 ');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
