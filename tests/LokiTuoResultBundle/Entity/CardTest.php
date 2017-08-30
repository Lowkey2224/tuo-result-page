<?php

namespace LokiTuoResultBundle\Entity;

use PHPUnit\Framework\TestCase;

class CardTest extends TestCase
{
    /**
     * @param array $levelsExist
     * @param $expected
     * @dataProvider levelProvider
     */
    public function testGetLevel(array $levelsExist, $desired, $expected)
    {
        $card = new Card();
        $levels = [];
        foreach ($levelsExist as $level) {
            $lvl = new CardLevel();
            $lvl->setCard($card);
            $lvl->setTuoId($level);
            $lvl->setLevel($level);
            $levels[] = $lvl;
        }
        $card->setLevels($levels);
        if (is_null($expected)) {
            $this->assertNull($card->getLevel($desired));
        } else {
            $this->assertEquals($expected, $card->getLevel($desired)->getLevel());
        }
    }

    public function levelProvider()
    {
        return [
            [[1, 2, 3, 4, 5], 6, null],
            [[1, 2, 3, 4, 5], 5, 5],
            [[3, 4, 1, 5, 2, 6], 5, 5],
            [[3, 4, 6, 5, 2, 1], 5, 5],
            [[3, 4, 6, 5, 2, 1], 6, 6],
            [[3, 4, 6, 5, 2, 1], null, 6],
        ];
    }
}
