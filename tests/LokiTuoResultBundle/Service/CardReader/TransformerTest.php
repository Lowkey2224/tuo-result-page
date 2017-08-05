<?php

namespace LokiTuoResultBundle\Service\CardReader;

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
		<skill id=\"evade\" x=\"6\"/>
		<skill id=\"enfeeble\" x=\"5\" all=\"1\"/>
		<skill id=\"strike\" x=\"5\" all=\"1\"/>
		<type>6</type>
		<set>2000</set>
		<upgrade>
			<card_id>34308</card_id>
			<level>2</level>
			<health>61</health>
			<skill id=\"evade\" x=\"6\"/>
			<skill id=\"enfeeble\" x=\"6\" all=\"1\"/>
			<skill id=\"strike\" x=\"6\" all=\"1\"/>
		</upgrade>
		<upgrade>
			<card_id>34309</card_id>
			<level>3</level>
			<health>64</health>
			<attack>12</attack>
		</upgrade>
		<upgrade>
			<card_id>34310</card_id>
			<level>4</level>
			<health>66</health>
			<skill id=\"evade\" x=\"7\"/>
			<skill id=\"enfeeble\" x=\"7\" all=\"1\"/>
			<skill id=\"strike\" x=\"6\" all=\"1\"/>
		</upgrade>
		<upgrade>
			<card_id>34311</card_id>
			<level>5</level>
			<health>69</health>
			<skill id=\"evade\" x=\"7\"/>
			<skill id=\"enfeeble\" x=\"7\" all=\"1\"/>
			<skill id=\"strike\" x=\"7\" all=\"1\"/>
		</upgrade>
		<upgrade>
			<card_id>34312</card_id>
			<level>6</level>
			<health>72</health>
			<attack>13</attack>
			<skill id=\"evade\" x=\"8\"/>
			<skill id=\"enfeeble\" x=\"7\" all=\"1\"/>
			<skill id=\"strike\" x=\"7\" all=\"1\"/>
		</upgrade>
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

        $l = new CardLevel();
        $l->setTuoId(34307)
            ->setAttack(11)
            ->setDefense(58)
            ->setPicture("falcor_lv1")
            ->setDelay(3);
        $levels[1] = $l;
        $this->assertEquals([$name => $expected], $cards);
    }
}

/*





 */