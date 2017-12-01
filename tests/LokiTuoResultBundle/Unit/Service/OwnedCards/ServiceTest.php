<?php


namespace LokiTuoResultBundle\Unit\Service\OwnedCards;


use Doctrine\ORM\EntityManager;
use LokiTuoResultBundle\Entity\Card;
use LokiTuoResultBundle\Entity\CardLevel;
use LokiTuoResultBundle\Entity\OwnedCard;
use LokiTuoResultBundle\Entity\Player;
use LokiTuoResultBundle\Repository\CardRepository;
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
        /** @var EntityManager $em */
        $service = new Service($em);
        $actual = $service->transformCardString($name);
        $expected = ['amount' => $amount, 'level' => $level, 'name' => $expectedName, 'inDeck' => $inDeck];
        $this->assertEquals($expected, $actual);
    }

    public function testTransformArrayToCardModel()
    {
        $player = new Player();
        $card = new Card();
        $card->setName("Infantry");
        $card->setRace(1);
        $card->setCardFile(null);
        $l = new CardLevel();
        $l->setLevel(1)
            ->setCard($card)
            ->setTuoId(1)
            ->setAttack(2)
            ->setDefense(2)
            ->setPicture("")
            ->setDelay("")
            ->setSkills([])
            ->setId(1);
        $cardWithoutLevel = new Card();
        $card->setLevels([$l]);
        $expected = new OwnedCard();
        $expected->setCard($l);
        $expected->setAmount(1);
        $expected->setPlayer($player);
        $expected->setAmountInDeck(0);
        $em = $this->createMock(EntityManager::class);
        $cardRepo = $this->createMock(CardRepository::class);
        $cardRepo->method('findOneBy')->will($this->onConsecutiveCalls(null, $card, $cardWithoutLevel));
        $em->method('getRepository')->willReturn($cardRepo);
        $ary = [
            ['name' => 'Deadalus', 'amount' => '1', 'level' => null, 'inDeck' => false],
            ['name' => 'Infantry', 'amount' => '1', 'level' => 1, 'inDeck' => false],
            ['name' => 'Trooper', 'amount' => '1', 'level' => 3, 'inDeck' => false],
        ];
        /** @var EntityManager $em */
        $service = new Service($em);
        $actual = $service->transformArrayToModels($player, $ary);
        $this->assertCount(1, $actual);
        $this->assertEquals([$expected], $actual);
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