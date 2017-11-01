<?php

namespace LokiTuoResultBundle\Unit\Service\TyrantApiConnector;

use LokiTuoResultBundle\Entity\KongregateCredentials;
use LokiTuoResultBundle\Entity\Player;
use LokiTuoResultBundle\Service\TyrantApiConnector\Service;
use LokiTuoResultBundle\Unit\Service\AbstractServiceTest;
use Psr\Log\NullLogger;

class GetInventoryTest extends AbstractServiceTest
{

    /** @var  Player */
    private $player;

    /**
     * @dataProvider dataProvider
     * @param $filename
     * @param $expectedCards
     */
    public function testGetInventory($filename, $expectedCards)
    {
        $service = new Service(new NullLogger());

        $mock = new \HTTP_Request2_Adapter_Mock();
        $response = "HTTP/1.1 200 OK\r\n\r\n";
        $response .= file_get_contents($this->getFilePath() . $filename);
        $mock->addResponse($response);
        $service->setRequestAdapter($mock);
        $ids = $service->getInventoryAndDeck($this->player);
        $this->assertEquals($expectedCards, $ids);
    }

    public function dataProvider()
    {
        $expectedCards = [];
        $expectedCards[2042] = $this->createCard(5, 0);
        $expectedCards[50116] = $this->createCard(0, 1);
        $expectedCards[25238] = $this->createCard(0, 1);
        $expectedCards[301] = $this->createCard(0, 1);
        $expectedCards[11714] = $this->createCard(0, 1);
        $expectedCards[34780] = $this->createCard(0, 2);
        $expectedCards[35929] = $this->createCard(0, 1);
        $expectedCards[42871] = $this->createCard(0, 1);
        $expectedCards[47481] = $this->createCard(0, 1);
        $expectedCards[48559] = $this->createCard(0, 1);
        $expectedCards[49261] = $this->createCard(0, 1);
        $expectedCards[55837] = $this->createCard(0, 1);
        return [
            ["getInventory.json", $expectedCards],
        ];
    }

    private function createCard($timesOwned, $timesUsed)
    {
        $c = [];
        if ($timesOwned) {
            $c['owned'] = $timesOwned;
        }
        if ($timesUsed) {
            $c['used'] = $timesUsed;
        }
        return $c;
    }

    protected function setUp()
    {
        parent::setUp();
        $this->player = new Player();
        $creds = new KongregateCredentials();
        $creds->setKongId("1")
            ->setKongPassword("1")
            ->setKongToken("1")
            ->setKongUserName("1")
            ->setSynCode("1")
            ->setTuUserId("1");
        $this->player->setKongCredentials($creds)
            ->setName("TestUser");
    }
}
