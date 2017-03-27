<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 28.11.16
 * Time: 16:18.
 */

namespace LokiUserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class Fixtures implements FixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $ary[]   = __DIR__.'/fixtures/users.yml';
        $objects = \Nelmio\Alice\Fixtures::load($ary, $manager);

        return $objects;
    }
}
