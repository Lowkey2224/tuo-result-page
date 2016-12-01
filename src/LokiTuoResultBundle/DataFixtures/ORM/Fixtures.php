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
            __DIR__ . "/fixtures/fixtures.yml",
        ];
        $objects = \Nelmio\Alice\Fixtures::load($ary, $manager);


        return $objects;
    }
}