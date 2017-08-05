<?php

namespace LokiTuoResultBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class CardLevel
 * @package LokiTuoResultBundle\Entity
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="LokiTuoResultBundle\Repository\CardLevelRepository")
 */
class CardLevel extends AbstractBaseEntity
{
    /**
     * @var int
     *
     * @ORM\Column(name="Attack", type="integer")
     */
    private $attack;

    /**
     * @var int
     *
     * @ORM\Column(name="Defense", type="integer")
     */
    private $defense;

    /**
     * @var int
     *
     * @ORM\Column(name="Delay", type="integer")
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
     * @ORM\Column(type="integer")
     */
    private $tuoId;

    /**
     * @return int
     */
    public function getAttack(): int
    {
        return $this->attack;
    }

    /**
     * @param int $attack
     * @return CardLevel
     */
    public function setAttack(int $attack)
    {
        $this->attack = $attack;
        return $this;
    }

    /**
     * @return int
     */
    public function getDefense(): int
    {
        return $this->defense;
    }

    /**
     * @param int $defense
     * @return CardLevel
     */
    public function setDefense(int $defense)
    {
        $this->defense = $defense;
        return $this;
    }

    /**
     * @return int
     */
    public function getDelay(): int
    {
        return $this->delay;
    }

    /**
     * @param int $delay
     * @return CardLevel
     */
    public function setDelay(int $delay)
    {
        $this->delay = $delay;
        return $this;
    }

    /**
     * @return string
     */
    public function getPicture(): string
    {
        return $this->picture;
    }

    /**
     * @param string $picture
     * @return CardLevel
     */
    public function setPicture(string $picture)
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
     * @param mixed $skills
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
    public function getTuoId(): int
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

}
