<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170323141519 extends AbstractMigration
{

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql',
            'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('INSERT INTO `guild` (`id`, `name`, `created_at`, `updated_at`) VALUES 
                (1, \'CTP\', NOW(), NOW()), 
                (2, \'CNS\', NOW(), NOW()), 
                (3, \'CTN\', NOW(), NOW()),
                (4, \'Elapse\', NOW(), NOW()),
                (5, \'-\', NOW(), NOW())
                ');
        $this->addSql('UPDATE player
        SET guild_id = IF(currentGuild = \'CTP\', 1,
        IF(currentGuild = \'CNS\', 2,
        IF(currentGuild = \'CTN\', 3,
        IF(currentGuild = \'Elapse\', 4, 
        5
        ))))');
        $this->addSql('UPDATE result
        SET guild_id = IF(guild = \'CTP\', 1,
        IF(guild = \'CNS\', 2,
        IF(guild = \'CTN\', 3,
        IF(guild = \'Elapse\', 4, 
        5
        ))))');

        $this->addSql('ALTER TABLE player DROP currentGuild');
        $this->addSql('ALTER TABLE result DROP guild');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql',
            'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE player ADD currentGuild VARCHAR(20) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE result ADD guild VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('UPDATE player
        SET currentGuild = IF(guild_id = 1, \'CTP\',
        IF(guild_id = 2, \'CNS\',
        IF(guild_id = 3 , \'CTN\',
        IF(guild_id = 4, \'Elapse\', 
        \'none\'
        ))))');
        $this->addSql('UPDATE result
        SET guild = IF(guild_id = 1, \'CTP\',
        IF(guild_id = 2, \'CNS\',
        IF(guild_id = 3 , \'CTN\',
        IF(guild_id = 4, \'Elapse\', 
        \'none\'
        ))))');
    }
}
