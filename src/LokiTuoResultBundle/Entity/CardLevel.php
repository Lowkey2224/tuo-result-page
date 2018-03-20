<?php

namespace App\LokiTuoResultBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class CardLevel
 * @package App\LokiTuoResultBundle\Entity
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="App\LokiTuoResultBundle\Repository\CardLevelRepository")
 */
class CardLevel extends AbstractBaseEntity
{
    const SKILL_DELIMITER = '|';

    /**
     * @var Card
     * @ORM\ManyToOne(targetEntity="Card", inversedBy="levels")
     * @ORM\JoinColumn(referencedColumnName="id", name="card_id")
     */
    private $card;

    /**
     * @var int
     *
     * @ORM\Column(name="Attack", type="integer", nullable=true)
     */
    private $attack;

    /**
     * @var int
     *
     * @ORM\Column(name="Defense", type="integer", nullable=true)
     */
    private $defense;

    /**
     * @var int
     *
     * @ORM\Column(name="Delay", type="integer", nullable=true)
     */
    private $delay;

    /**
     * @var string
     *
     * @ORM\Column(name="Picture", type="string", length=255, nullable=true)
     */
    private $picture;

    /**
     * @var
     * @ORM\Column(type="array", nullable=true)
     */
    private $skills;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=false)
     */
    private $level;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $tuoId;

    public function __toString()
    {
        $str = $this->getCard()->getName();
        $str .= ' ';
        $str .= $this->getDelay();
        $str .= '/';
        $str .= $this->getAttack();
        $str .= '/';
        $str .= $this->getDefense();
        $str .= ' ';

        foreach ($this->getSkills() as $skill) {
            $str .= $skill . ' ';
        }
        $str .= ' (' . $this->getCard()->getRaceName() . ')';

        return $str;
    }

    /**
     * Returns the name of the Card.
     * Maybe needs to fetch getCard()
     * @return string
     */
    public function getName()
    {
        return $this->card->getName();
    }

    public function __construct()
    {
        $this->skills = [];
    }

    /**
     * @return int
     */
    public function getAttack()
    {
        return $this->attack;
    }

    /**
     * @param int $attack
     * @return CardLevel
     */
    public function setAttack($attack)
    {
        $this->attack = $attack;
        return $this;
    }

    /**
     * @return int
     */
    public function getDefense()
    {
        return $this->defense;
    }

    /**
     * @param int $defense
     * @return CardLevel
     */
    public function setDefense($defense)
    {
        $this->defense = $defense;
        return $this;
    }

    /**
     * @return int
     */
    public function getDelay()
    {
        return $this->delay;
    }

    /**
     * @param int $delay
     * @return CardLevel
     */
    public function setDelay($delay)
    {
        $this->delay = $delay;
        return $this;
    }

    /**
     * @return string
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * @param string $picture
     * @return CardLevel
     */
    public function setPicture($picture)
    {
        $this->picture = $picture;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSkills()
    {
        return $this->skills;
    }

    /**
     * @param String[] $skills
     * @return CardLevel
     */
    public function setSkills($skills)
    {
        $this->skills = $skills;
        return $this;
    }

    /**
     * @return int
     */
    public function getTuoId()
    {
        return $this->tuoId;
    }

    /**
     * @param int $tuoId
     * @return CardLevel
     */
    public function setTuoId(int $tuoId)
    {
        $this->tuoId = $tuoId;
        return $this;
    }

    /**
     * @return Card
     */
    public function getCard(): Card
    {
        return $this->card;
    }

    /**
     * @param Card $card
     * @return CardLevel
     */
    public function setCard(Card $card)
    {
        $this->card = $card;
        return $this;
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @param int $level
     * @return CardLevel
     */
    public function setLevel(int $level)
    {
        $this->level = $level;
        return $this;
    }


}
