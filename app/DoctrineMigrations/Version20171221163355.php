<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

//use LokiTuoResultBundle\Entity\BattleLog;
//use LokiTuoResultBundle\Entity\Message;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171221163355 extends AbstractMigration implements ContainerAwareInterface
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

        $this->addSql('DROP TABLE message');
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

        $this->addSql('CREATE TABLE message (
          id INT AUTO_INCREMENT NOT NULL, 
          player_id INT DEFAULT NULL, 
          status INT NOT NULL, 
          message VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, 
          created_at DATETIME DEFAULT NULL, 
          updated_at DATETIME DEFAULT NULL, 
          INDEX IDX_B6BD307F99E6F5DF (player_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          message 
        ADD 
          CONSTRAINT FK_B6BD307F99E6F5DF FOREIGN KEY (player_id) REFERENCES player (id)');
    }

//    public function postDown(Schema $schema)
//    {
//        parent::postDown($schema);
//        $man = $this->container->get('doctrine.orm.entity_manager');
//        $msgs = $man->getRepository('LokiTuoResultBundle:BattleLog')->findAll();
//        foreach ($msgs as $msg) {
//            $matches = [];
//            preg_match_all('/[a-zA-Z]+ (\d+)/', $msg->getMessage(), $matches);
//            $f = $matches[1][0];
//            $w = $matches[1][1];
//            $g = $matches[1][2];
//            $r = $matches[1][3];
//            if($f > 0){
//                $bl = new Message();
//                $bl->setMessage(sprintf(" Fought %d battles and won %d of them. Won %d gold and %d rating", $f, $w, $g, $r));
//                $bl->setPlayer($msg->getPlayer());
//                $bl->setStatus($msg->isRead()?BattleLog::STATUS_READ:BattleLog::STATUS_UNREAD);
//                $man->persist($bl);
//            }
//        }
//        $man->flush();
//    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
