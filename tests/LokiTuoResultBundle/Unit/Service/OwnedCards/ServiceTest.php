<?php


namespace LokiTuoResultBundle\Unit\Service\OwnedCards;


use Doctrine\ORM\EntityManager;
use LokiTuoResultBundle\Service\OwnedCards\Service;
use PHPUnit\Framework\TestCase;

class ServiceTest extends TestCase
{
    /**
     * @dataProvider cards
     * @param $name
     * @param $amount
     * @param $level
     * @param bool $inDeck
     */
    public function testTransformCardString($name, $expectedName, $amount, $level, $inDeck = false)
    {
        $em = $this->createMock(EntityManager::class);
        $service = new Service($em);
        $actual = $service->transformCardString($name);
        $expected = ['amount' => $amount, 'level' => $level, 'name' => $expectedName, 'inDeck' => $inDeck];
        $this->assertEquals($expected, $actual);
    }

    public static function cards()
    {
        return [
            ["Infantry-3(2)", "Infantry", 2, 3, 0],
            ["TR-16-3(2)", "TR-16", 2, 3, 0],
            ["TR-16", "TR-16", 1, null, 0],
            ["TR-Foo", "TR-Foo", 1, null, 0],
        ];
    }
}