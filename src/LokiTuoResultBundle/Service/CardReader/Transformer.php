<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 11.08.16
 * Time: 10:19
 */

namespace LokiTuoResultBundle\Service\CardReader;

use LokiTuoResultBundle\Entity\Card;
use LokiTuoResultBundle\Entity\CardFile;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Transformer
{
    /** @var LoggerInterface */
    private $logger;

    public function __construct()
    {
        $this->logger = new NullLogger();
    }

    /**
     * @param $content
     * @return Card[]
     */
    public function transformToModels($content, CardFile $file, $result = [])
    {
        foreach ($content as $object) {
            if (array_key_exists(trim($object->name), $result)) {
                continue;
            }
            $skills=[];
            $card = new Card();
            $card->setName(trim($object->name));
            $card->setPicture($object->picture);
            $card->setAttack($object->attack);
            $card->setDefense($object->health);
            $card->setDelay($object->cost);
            $card->setCardFile($file);
            if (isset($object->skill)) {
                $skills = array_merge($skills, $this->readSkill($object->skill));
                $card->setSkills($skills);
            }
            if (isset($object->upgrade)) {
                $card = $this->readUpgrades($card, $object->upgrade, $skills);
            } else {
                $this->logger->debug('Card without Upgrade found: ' . $card->getName());
            }
            $result[$card->getName()] = $card;
        }

        return $result;
    }

    private function readUpgrades(Card $card, $upgrades, $skills)
    {
        foreach ($upgrades as $upgrade) {
            if (isset($upgrade->picture)) {
                $card->setPicture($upgrade->picture);
            }
            if (isset($upgrade->health)) {
                $card->setDefense($upgrade->health);
            };
            if (isset($upgrade->attack)) {
                $card->setAttack($upgrade->attack);
            };
            if (isset($upgrade->cost)) {
                $card->setDelay($upgrade->cost);
            }
            if (isset($upgrade->skill)) {
                $skills = array_merge($skills, $this->readSkill($upgrade->skill));
                $card->setSkills($skills);
            }
        }
        return $card;
    }

    private function readSkill($skills)
    {

        $res = [];
        foreach ($skills as $skill) {
            $id = trim($skill['id']);
            $all = isset($skill['all']);
            $enhancedSkill = (isset($skill['s'])) ? $skill['s'] : "";
            $evolvedSkill = (isset($skill['s2'])) ? $skill['s2'] : "";
            $countdown = (isset($skill['c'])) ? $skill['c'] : "";
            $race = $skill['y'];
            $skillLevel = (isset($skill['x'])) ? $skill['x'] : "";
            $amountOfCards = (isset($skill['n'])) ? $skill['n'] : 1;
            $amountOfCards = ($all) ? "all" : $amountOfCards;
            switch ($id) {
                case 'enhance':
                    $str = $id . " " . $amountOfCards . " " . $enhancedSkill . " " . $skillLevel;
                    break;
                case 'evolve':
                    $str = $id . " " . $amountOfCards . " " . $enhancedSkill . " to " . $evolvedSkill;
                    break;
                default:
                    $str = $id . " " . $amountOfCards . " " . $this->getFactionName($race) . " " . $skillLevel;
                    $str .= ($countdown) ? " every " . $countdown : "";
            }
            $res[$id] = $str;
        }
        return $res;
    }

    private function getFactionName($factionId)
    {
        switch ($factionId) {
            case 1:
                return "Imperial";
            case 2:
                return "Raider";
            case 3:
                return "Bloodthirsty";
            case 4:
                return "Xeno";
            case 5:
                return "Righteous";
            case 6:
                return "Progenitor";
            default:
                return $factionId;
        }
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }
}
