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
class Version20170323141519 extends AbstractMigration implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    private $guildMap = [
        'CTP' => 1,
        'CNS' => 2,
        'CTN' => 3,
        'Elapse' => 4,
        'Inactive' => 5,
    ];

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
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    private function createGuilds()
    {
        $guilds = [];
        $g = new Guild();
        $g->setName('CTP');
        $g->setId(1);
        $guilds[1] = $g;
        $g = new Guild();
        $g->setName('CNS');
        $g->setId(2);
        $guilds[2] = $g;
        $g = new Guild();
        $g->setName('CTN');
        $g->setId(3);
        $guilds[3] = $g;
        $g = new Guild();
        $g->setName('Elapse');
        $g->setId(4);
        $guilds[4] = $g;
        $g = new Guild();
        $g->setName('-');
        $g->setId(5);
        $guilds[5] = $g;
        $em = $this->container->get('doctrine')->getManager();
        foreach ($guilds as $guild) {
            $em->persist($guild);
        }
        $em->flush();

        return $guilds;
    }

    /**
     * @param Guild[] $guilds
     */
    private function updatePlayers(array $guilds)
    {
        $em = $this->container->get('doctrine')->getManager();
        $players = $em->getRepository('LokiTuoResultBundle:Player')->findAll();
        foreach ($players as $player) {
            $id = $this->guildMap[$player->getCurrentGuild()];
            $player->setGuild($guilds[$id]);
            $em->persist($player);
        }
        $em->flush();
    }

    /**
     * @param Guild[] $guilds
     */
    private function updateResults(array $guilds)
    {
        $em = $this->container->get('doctrine')->getManager();
        $results = $em->getRepository('LokiTuoResultBundle:Result')->findAll();
        /** @var Result $result */
        foreach ($results as $result) {
            $id = $this->guildMap[$result->getGuild()];
            $result->setDbGuild($guilds[$id]);
            $em->persist($result);
        }
        $em->flush();
    }
}
