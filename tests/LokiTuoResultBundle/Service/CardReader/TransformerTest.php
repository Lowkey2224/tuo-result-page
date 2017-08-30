<?php

namespace LokiTuoResultBundle\Service\CardReader;

use Doctrine\Common\Collections\ArrayCollection;
use LokiTuoResultBundle\Entity\Card;
use LokiTuoResultBundle\Entity\CardFile;
use LokiTuoResultBundle\Entity\CardLevel;
use PHPUnit\Framework\TestCase;

class TransformerTest extends TestCase
{
    public function testTransformToModels()
    {
        $xml = "<root><unit>
		<id>34307</id>
		<name>Tetrapede Comber</name> <!-- Tetrapede -->
		<picture>falcor_lv1</picture>
		<asset_bundle>152</asset_bundle>
		<fusion_level>1</fusion_level>
		<attack>11</attack>
		<health>58</health>
		<cost>3</cost>
		<rarity>5</rarity>
		<type>6</type>
		<set>2000</set>
	</unit></root>";


        $content = simplexml_load_string($xml);
        $transformer = new Transformer();
        /** @var CardFile $file */
        $file = $this->getMockBuilder(CardFile::class)->getMock();
        $cards = $transformer->transformToModels($content, $file);
        $expected = new Card();
        $name = "Tetrapede Comber";
        $expected->setName($name);
        $expected->setRace(6);
        $expected->setCardFile($file);

        $levels = new ArrayCollection();
        $l = new CardLevel();
        $l->setTuoId(34307)
            ->setAttack(11)
            ->setDefense(58)
            ->setPicture("falcor_lv1")
            ->setDelay(3);
        $levels->add($l);
        $this->assertEquals([$name => $expected], $cards);
    }
}

/*





 */