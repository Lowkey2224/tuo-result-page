<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 11.08.16
 * Time: 10:19.
 */

namespace LokiTuoResultBundle\Service\CardReader;

use Doctrine\Common\Collections\ArrayCollection;
use LokiTuoResultBundle\Entity\Card;
use LokiTuoResultBundle\Entity\CardFile;
use LokiTuoResultBundle\Entity\CardLevel;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

class Transformer
{
    use LoggerAwareTrait;

    /**
     * Transformer constructor.
     */
    public function __construct()
    {
        $this->logger = new NullLogger();
    }

    /**
     * @param $content
     * @param mixed $result
     *
     * @return Card[]
     */
    public function transformToModels($content, CardFile $file, $result = [])
    {
        foreach ($content as $object) {
            if (array_key_exists(trim($object->name), $result)) {
                continue;
            }
            $skills = [];
            $card = new Card();
            $card->setName(trim($object->name));
            $card->setRace((int)$object->type);
            $card->setCardFile($file);
            $level1 = new CardLevel();
            $level1->setPicture($object->picture)
                ->setAttack(($object->attack) ? (int)$object->attack : 0)
                ->setDefense(($object->health) ? (int)$object->health : 0)
                ->setDelay(($object->cost) ? (int)$object->cost : 0)
                ->setTuoId((int)$object->id)
                ->setLevel(1)
            ->setCard($card);
            if (isset($object->skill)) {
                $skills = array_merge($skills, $this->readSkill($object->skill));
                $level1->setSkills($skills);
            }
            if (isset($object->upgrade)) {
                $levels = $this->readUpgrades($card, $object->upgrade, $skills);
            } else {
                $levels = new ArrayCollection();
                $this->logger->debug('Card without Upgrade found: ' . $card->getName());
            }

            $levels->add($level1);
            $card->setLevels($levels);
            $result[$card->getName()] = $card;
        }

        return $result;
    }

    /**
     * Read the Upgrades of a Card.
     *
     * @param Card $card
     * @param $upgrades
     * @param $skills
     *
     * @return CardLevel[]|ArrayCollection
     */
    private function readUpgrades(Card $card, $upgrades, $skills)
    {
        $levels = new ArrayCollection();
        foreach ($upgrades as $upgrade) {
            $level = new CardLevel();
            $level->setCard($card);
            $level->setTuoId((int)$upgrade->card_id);
            $level->setLevel((int)$upgrade->level);
            if (isset($upgrade->picture)) {
                $level->setPicture((string)$upgrade->picture);
            }
            if (isset($upgrade->health)) {
                $level->setDefense((int)$upgrade->health);
            }
            if (isset($upgrade->attack)) {
                $level->setAttack((int)$upgrade->attack);
            }
            if (isset($upgrade->cost)) {
                $level->setDelay((int)$upgrade->cost);
            }
            if (isset($upgrade->skill)) {
                $skills = array_merge($skills, $this->readSkill($upgrade->skill));
                $level->setSkills($skills);
            }
            $levels->add($level);
        }

        return $levels;
    }

    /**
     * Read an Array of Skill.
     *
     * @param $skills
     *
     * @return array
     */
    private function readSkill($skills)
    {
        $res = [];
        foreach ($skills as $skill) {
            $id = trim($skill['id']);

            $enhancedSkill = $this->getEnhancedSkill($skill);
            $evolvedSkill = $this->getEvolvedToSkill($skill);
            $countdown = $this->getCountDown($skill);
            $race = $skill['y'];
            $skillLevel = $this->getSkillLevel($skill);
            $amountOfCards = $this->getAmountOfCards($skill);

            switch ($id) {
                case 'enhance':
                    $str = $id . ' ' . $amountOfCards . ' ' . $enhancedSkill . ' ' . $skillLevel;
                    break;
                case 'evolve':
                    $str = $id . ' ' . $amountOfCards . ' ' . $enhancedSkill . ' to ' . $evolvedSkill;
                    break;
                default:
                    $str = $id . ' ' . $amountOfCards . ' ' . Card::getFactionName($race) . ' ' . $skillLevel;
                    $str .= ($countdown) ? ' every ' . $countdown : '';
            }
            $res[$id] = $str;
        }

        return $res;
    }

    /**
     * Get the name of the Enhanced Skill.
     *
     * @param $skill
     *
     * @return string
     */
    private function getEnhancedSkill($skill)
    {
        return (isset($skill['s'])) ? $skill['s'] : '';
    }

    /**
     * Get the name of the Skill it is evolved to (e.G. Venom).
     *
     * @param $skill
     *
     * @return string
     */
    private function getEvolvedToSkill($skill)
    {
        return (isset($skill['s2'])) ? $skill['s2'] : '';
    }

    /**
     * Get the countdown/cooldown of a skill.
     *
     * @param $skill
     *
     * @return string
     */
    private function getCountDown($skill)
    {
        return (isset($skill['c'])) ? $skill['c'] : '';
    }

    /**
     * Get The Skilllevel.
     *
     * @param $skill
     *
     * @return string
     */
    private function getSkillLevel($skill)
    {
        return (isset($skill['x'])) ? $skill['x'] : '';
    }

    /**
     * The the amount of Cards affected by the skill.
     *
     * @param $skill
     *
     * @return string
     */
    private function getAmountOfCards($skill)
    {
        $all = isset($skill['all']);
        $amountOfCards = (isset($skill['n'])) ? $skill['n'] : '';

        return ($all) ? 'all' : $amountOfCards;
    }
}
