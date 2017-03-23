<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 28.11.16
 * Time: 16:18
 */

namespace LokiTuoResultBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Fixtures implements FixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }


    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {

        $ary = [
            __DIR__ . "/fixtures/guilds.yml",
            __DIR__ . "/fixtures/cardfile.yml",
            __DIR__ . "/fixtures/cards.yml",
            __DIR__ . "/fixtures/players.yml",
            __DIR__ . "/fixtures/ownedCards.yml",
            __DIR__ . "/fixtures/bges.yml",
            __DIR__ . "/fixtures/resultFile.yml",
            __DIR__ . "/fixtures/missions.yml",
            __DIR__ . "/fixtures/result.yml",
            __DIR__ . "/fixtures/deckEntry.yml",
        ];
        $objects = \Nelmio\Alice\Fixtures::load($ary, $manager);


        return $objects;
    }
}
