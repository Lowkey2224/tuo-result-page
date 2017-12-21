<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use LokiTuoResultBundle\Entity\BattleLog;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171221123347 extends AbstractMigration implements ContainerAwareInterface
{
    /** @var ContainerInterface */
    private $container;

    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Migrations\AbortMigrationException
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE battle_log (
          id INT AUTO_INCREMENT NOT NULL, 
          player_id INT DEFAULT NULL, 
          battles INT NOT NULL, 
          won INT NOT NULL, 
          gold INT NOT NULL, 
          rating INT NOT NULL, 
          status INT NOT NULL, 
          created_at DATETIME DEFAULT NULL, 
          updated_at DATETIME DEFAULT NULL, 
          INDEX IDX_8049DBB199E6F5DF (player_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          battle_log 
        ADD 
          CONSTRAINT FK_8049DBB199E6F5DF FOREIGN KEY (player_id) REFERENCES player (id)');
        $this->addSql('ALTER TABLE message CHANGE status status INT NOT NULL');
//        $this->
    }

    public function postUp(Schema $schema)
    {
        parent::postUp($schema);
        $man = $this->container->get('doctrine.orm.entity_manager');
        $msgs = $man->getRepository('LokiTuoResultBundle:Message')->findAll();
        foreach ($msgs as $msg) {
            $matches = [];
            preg_match_all('/[a-zA-Z]+ (\d+)/', $msg->getMessage(), $matches);
            $f = $matches[1][0];
            $w = $matches[1][1];
            $g = $matches[1][2];
            $r = $matches[1][3];
            if ($f > 0) {
                $bl = new BattleLog();
                $bl->setBattles($f);
                $bl->setGold($g);
                $bl->setRating($r);
                $bl->setWon($w);
                $bl->setPlayer($msg->getPlayer());
                $bl->setStatus($msg->isRead() ? BattleLog::STATUS_READ : BattleLog::STATUS_UNREAD);
                $man->persist($bl);
            }
        }
        $man->flush();
    }

    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Migrations\AbortMigrationException
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE battle_log');
        $this->addSql('ALTER TABLE message CHANGE status status INT NOT NULL COMMENT \'1 => Ungelesen, 2 => Gelesen\'');
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
